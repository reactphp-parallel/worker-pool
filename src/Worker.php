<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker;

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\ClosedException;
use ReactParallel\Contracts\GroupInterface;
use ReactParallel\Contracts\LowLevelPoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Worker\Work\WorkerFactory;
use WyriHaximus\PoolInfo\Info;
use WyriHaximus\PoolInfo\PoolInfoInterface;

use function array_key_exists;
use function array_pop;
use function assert;
use function count;
use function is_string;
use function React\Promise\reject;
use function spl_object_hash;

use const WyriHaximus\Constants\Boolean\FALSE_;
use const WyriHaximus\Constants\Boolean\TRUE_;
use const WyriHaximus\Constants\Numeric\ZERO;

final class Worker implements PoolInfoInterface
{
    public const MINIMUM_TTL = 0.1;
    private LoopInterface $loop;
    private EventLoopBridge $eventLoopBridge;
    private LowLevelPoolInterface $pool;
    private WorkerFactory $worker;
    private float $ttl;
    private GroupInterface $group;

    /** @var Thread[] */
    private array $threads = [];

    /** @var string[] */
    private array $idleThreads = [];

    /** @var TimerInterface[] */
    private array $ttlTimers = [];

    private bool $closed = FALSE_;

    public function __construct(LoopInterface $loop, EventLoopBridge $eventLoopBridge, LowLevelPoolInterface $pool, WorkerFactory $worker, float $ttl)
    {
        $this->loop            = $loop;
        $this->eventLoopBridge = $eventLoopBridge;
        $this->pool            = $pool;
        $this->worker          = $worker;
        $this->ttl             = $ttl;
        $this->group           = $this->pool->acquireGroup();
    }

    public function perform(Work $work): PromiseInterface
    {
        if ($this->closed === TRUE_) {
            return reject(ClosedException::create());
        }

        return (new Promise(function (callable $resolve): void {
            if (count($this->idleThreads) === ZERO) {
                $resolve($this->spawnRuntime());

                return;
            }

            $resolve($this->getIdleThread());
        }))->then(function (Thread $thread) use ($work): PromiseInterface {
            /** @psalm-suppress UndefinedInterfaceMethod */
            return $thread->perform($work)->always(function () use ($thread): void {
                if ($this->ttl >= self::MINIMUM_TTL) {
                    $this->addThreadToIdleList($thread);
                    $this->startTtlTimer($thread);

                    return;
                }

                $this->closeThread(spl_object_hash($thread));
            });
        });
    }

    public function close(): bool
    {
        $this->closed = TRUE_;

        foreach ($this->threads as $hash => $runtime) {
            $this->closeThread($hash);
        }

        $this->pool->releaseGroup($this->group);
        $this->pool->close();

        return TRUE_;
    }

    public function kill(): bool
    {
        $this->closed = TRUE_;

        foreach ($this->threads as $hash => $runtime) {
            $this->closeThread($hash);
        }

        $this->pool->releaseGroup($this->group);
        $this->pool->kill();

        return TRUE_;
    }

    /**
     * @return iterable<string, int>
     */
    public function info(): iterable
    {
        yield Info::TOTAL => count($this->threads);
        yield Info::BUSY => count($this->threads) - count($this->idleThreads);
        yield Info::CALLS => ZERO;
        yield Info::IDLE  => count($this->idleThreads);
        yield Info::SIZE  => count($this->threads);
    }

    private function getIdleThread(): Thread
    {
        $hash = array_pop($this->idleThreads);
        assert(is_string($hash));

        if (array_key_exists($hash, $this->ttlTimers) === TRUE_) {
            $this->loop->cancelTimer($this->ttlTimers[$hash]);
            unset($this->ttlTimers[$hash]);
        }

        return $this->threads[$hash];
    }

    private function addThreadToIdleList(Thread $thread): void
    {
        $hash                     = spl_object_hash($thread);
        $this->idleThreads[$hash] = $hash;
    }

    private function spawnRuntime(): Thread
    {
        $thread                                  = Thread::create($this->worker, $this->eventLoopBridge, $this->pool);
        $this->threads[spl_object_hash($thread)] = $thread;

        return $thread;
    }

    private function startTtlTimer(Thread $thread): void
    {
        $hash = spl_object_hash($thread);

        $this->ttlTimers[$hash] = $this->loop->addTimer($this->ttl, function () use ($hash): void {
            $this->closeThread($hash);
        });
    }

    private function closeThread(string $hash): void
    {
        $runtime = $this->threads[$hash];
        $runtime->close();

        unset($this->threads[$hash]);

        if (array_key_exists($hash, $this->idleThreads) === TRUE_) {
            unset($this->idleThreads[$hash]);
        }

        if (array_key_exists($hash, $this->ttlTimers) === FALSE_) {
            return;
        }

        $this->loop->cancelTimer($this->ttlTimers[$hash]);

        unset($this->ttlTimers[$hash]);
    }
}
