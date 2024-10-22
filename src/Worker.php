<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker;

use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use ReactParallel\Contracts\ClosedException;
use ReactParallel\Contracts\GroupInterface;
use ReactParallel\Contracts\LowLevelPoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Factory;
use ReactParallel\Pool\Worker\Work\Work;
use ReactParallel\Pool\Worker\Work\WorkerFactory;
use WyriHaximus\PoolInfo\Info;
use WyriHaximus\PoolInfo\PoolInfoInterface;

use function array_key_exists;
use function array_pop;
use function assert;
use function count;
use function is_string;
use function spl_object_id;

use const WyriHaximus\Constants\Boolean\FALSE_;
use const WyriHaximus\Constants\Boolean\TRUE_;
use const WyriHaximus\Constants\Numeric\ZERO;

/**
 * @template TResult of object
 * @template TWork of Work
 * @template TWorkerFactory of WorkerFactory
 */
final class Worker implements PoolInfoInterface
{
    public const MINIMUM_TTL = 0.1;
    private EventLoopBridge $eventLoopBridge;

    private LowLevelPoolInterface $pool;

    private GroupInterface $group;

    /** @var array<int, Thread<I, O>> */
    private array $threads = [];

    /** @var array<string> */
    private array $idleThreads = [];

    /** @var array<TimerInterface> */
    private array $ttlTimers = [];

    private bool $closed = FALSE_;

    /** @param TWorkerFactory $workerFactory */
    public function __construct(Factory $factory, private WorkerFactory $workerFactory, private float $ttl)
    {
        $this->eventLoopBridge = $factory->eventLoopBridge();
        $this->pool            = $factory->lowLevelPool();
        $this->group           = $this->pool->acquireGroup();
    }

    /**
     * @param TWork $work
     *
     * @return TResult
     */
    public function perform(Work $work): object
    {
        if ($this->closed === TRUE_) {
            throw ClosedException::create();
        }

        $thread = $this->getFirstAvailableThread();

        try {
            $result = $thread->perform($work);
        } finally {
            Loop::futureTick(fn () => $this->returnOrCleanUpThread($thread));
        }

        return $result->result();
    }

    public function close(): bool
    {
        $this->closed = TRUE_;

        foreach ($this->threads as $id => $runtime) {
            $this->closeThread($id);
        }

        $this->pool->releaseGroup($this->group);
        $this->pool->close();

        return TRUE_;
    }

    public function kill(): bool
    {
        $this->closed = TRUE_;

        foreach ($this->threads as $id => $runtime) {
            $this->closeThread($id);
        }

        $this->pool->releaseGroup($this->group);
        $this->pool->kill();

        return TRUE_;
    }

    /** @return iterable<string, int> */
    public function info(): iterable
    {
        yield Info::TOTAL => count($this->threads);
        yield Info::BUSY => count($this->threads) - count($this->idleThreads);
        yield Info::CALLS => ZERO;
        yield Info::IDLE  => count($this->idleThreads);
        yield Info::SIZE  => count($this->threads);
    }

    private function getFirstAvailableThread(): Thread
    {
        if (count($this->idleThreads) === ZERO) {
            return $this->spawnRuntime();
        }

        return $this->getIdleThread();
    }

    private function returnOrCleanUpThread(Thread $thread): void
    {
        if ($this->ttl >= self::MINIMUM_TTL) {
            $this->addThreadToIdleList($thread);
            $this->startTtlTimer($thread);
        } else {
            $this->closeThread(spl_object_id($thread));
        }
    }

    private function getIdleThread(): Thread
    {
        $id = array_pop($this->idleThreads);
        assert(is_string($id));

        if (array_key_exists($id, $this->ttlTimers) === TRUE_) {
            Loop::cancelTimer($this->ttlTimers[$id]);
            unset($this->ttlTimers[$id]);
        }

        return $this->threads[$id];
    }

    private function addThreadToIdleList(Thread $thread): void
    {
        $id                     = spl_object_id($thread);
        $this->idleThreads[$id] = $id;
    }

    private function spawnRuntime(): Thread
    {
        $thread                                = Thread::create($this->workerFactory, $this->eventLoopBridge, $this->pool);
        $this->threads[spl_object_id($thread)] = $thread;

        return $thread;
    }

    private function startTtlTimer(Thread $thread): void
    {
        $id = spl_object_id($thread);

        $this->ttlTimers[$id] = Loop::addTimer($this->ttl, function () use ($id): void {
            $this->closeThread($id);
        });
    }

    private function closeThread(int $id): void
    {
        $runtime = $this->threads[$id];
        $runtime->close();

        unset($this->threads[$id]);

        if (array_key_exists($id, $this->idleThreads) === TRUE_) {
            unset($this->idleThreads[$id]);
        }

        if (array_key_exists($id, $this->ttlTimers) === FALSE_) {
            return;
        }

        Loop::cancelTimer($this->ttlTimers[$id]);

        unset($this->ttlTimers[$id]);
    }
}
