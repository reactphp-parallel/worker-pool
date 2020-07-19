<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Pool\Worker;

use Money\Money;
use React\EventLoop\Factory;
use ReactParallel\Contracts\LowLevelPoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Group;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Worker\Worker;
use ReactParallel\Pool\Worker\Workers\ReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\ThrowingReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\ThrownWork;
use ReactParallel\Pool\Worker\Workers\Work;
use Throwable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\PoolInfo\Info;

use function assert;
use function iterator_to_array;

final class WorkerTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function return(): void
    {
        $loop            = Factory::create();
        $eventLoopBridge = new EventLoopBridge($loop);

        $workerFactory = new ReturnWorkerFactory();

        $pool = new Worker($loop, $eventLoopBridge, new Infinite($loop, $eventLoopBridge, 1), $workerFactory, 1);

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 0,
            Info::SIZE => 0,
        ], iterator_to_array($pool->info()));

        $loop->futureTick(static function () use ($pool): void {
            self::assertSame([
                Info::TOTAL => 1,
                Info::BUSY => 1,
                Info::CALLS => 0,
                Info::IDLE => 0,
                Info::SIZE => 1,
            ], iterator_to_array($pool->info()));
        });

        $money = $this->await($pool->perform(new Work(Money::EUR(512)))->always(static function () use ($pool): void {
            $pool->close();
        }), $loop);
        assert($money instanceof Money);

        self::assertSame('512', $money->getAmount());

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 0,
            Info::SIZE => 0,
        ], iterator_to_array($pool->info()));
    }

    /**
     * @test
     */
    public function thrown(): void
    {
        $loop            = Factory::create();
        $eventLoopBridge = new EventLoopBridge($loop);

        $workerFactory = new ThrowingReturnWorkerFactory();

        $pool = new Worker($loop, $eventLoopBridge, new Infinite($loop, $eventLoopBridge, 1), $workerFactory, 1);

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 0,
            Info::SIZE => 0,
        ], iterator_to_array($pool->info()));

        $loop->futureTick(static function () use ($pool): void {
            self::assertSame([
                Info::TOTAL => 1,
                Info::BUSY => 1,
                Info::CALLS => 0,
                Info::IDLE => 0,
                Info::SIZE => 1,
            ], iterator_to_array($pool->info()));
        });

        $money = null;
        try {
            $this->await($pool->perform(new Work(Money::EUR(512)))->always(static function () use ($pool): void {
                $pool->close();
            }), $loop);
        } catch (ThrownWork $thrownWork) {
            $money = $thrownWork->work();
        } catch (Throwable $throwable) {
            throw $throwable;
        }

        self::assertNotNull($money);
        self::assertInstanceOf(Money::class, $money);
        self::assertSame('512', $money->getAmount());

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 0,
            Info::SIZE => 0,
        ], iterator_to_array($pool->info()));
    }

    /**
     * @test
     */
    public function close(): void
    {
        $loop            = Factory::create();
        $eventLoopBridge = new EventLoopBridge($loop);

        $group         = Group::create();
        $workerFactory = new ThrowingReturnWorkerFactory();
        $pool          = $this->prophesize(LowLevelPoolInterface::class);
        $pool->acquireGroup()->shouldBeCalled()->willReturn($group);
        $pool->releaseGroup($group)->shouldBeCalled();
        $pool->close()->shouldBeCalled();

        $pool = new Worker($loop, $eventLoopBridge, $pool->reveal(), $workerFactory, 1);
        $pool->close();
    }

    /**
     * @test
     */
    public function kill(): void
    {
        $loop            = Factory::create();
        $eventLoopBridge = new EventLoopBridge($loop);

        $group         = Group::create();
        $workerFactory = new ThrowingReturnWorkerFactory();
        $pool          = $this->prophesize(LowLevelPoolInterface::class);
        $pool->acquireGroup()->shouldBeCalled()->willReturn($group);
        $pool->releaseGroup($group)->shouldBeCalled();
        $pool->kill()->shouldBeCalled();

        $pool = new Worker($loop, $eventLoopBridge, $pool->reveal(), $workerFactory, 1);
        $pool->kill();
    }
}
