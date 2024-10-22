<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Pool\Worker;

use Money\Money;
use React\EventLoop\Loop;
use ReactParallel\Contracts\LowLevelPoolInterface;
use ReactParallel\Factory as ParallelFactory;
use ReactParallel\Pool\Infinite\Group;
use ReactParallel\Pool\Worker\Worker;
use ReactParallel\Pool\Worker\Workers\ReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\ThrowingReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\ThrownWork;
use ReactParallel\Pool\Worker\Workers\WorkObject;
use ReflectionClass;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\PoolInfo\Info;

final class WorkerTest extends AsyncTestCase
{
    /** @test */
    public function return(): void
    {
        $parallelFactory = new ParallelFactory();

        $workerFactory = new ReturnWorkerFactory();

        $pool = new Worker($parallelFactory, $workerFactory, 1);

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 0,
            Info::SIZE => 0,
        ], [...$pool->info()]);

        Loop::futureTick(static function () use ($pool): void {
            self::assertSame([
                Info::TOTAL => 1,
                Info::BUSY => 1,
                Info::CALLS => 0,
                Info::IDLE => 0,
                Info::SIZE => 1,
            ], [...$pool->info()]);
        });

        try {
            $money = $pool->perform(new WorkObject(Money::EUR(512)));
        } finally {
            $pool->close();
        }

        self::assertSame('512', $money->getAmount());

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 0,
            Info::SIZE => 0,
        ], [...$pool->info()]);
    }

    /** @test */
    public function thrown(): void
    {
        $parallelFactory = new ParallelFactory();

        $workerFactory = new ThrowingReturnWorkerFactory();

        $pool = new Worker($parallelFactory, $workerFactory, 1);

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 0,
            Info::SIZE => 0,
        ], [...$pool->info()]);

        Loop::futureTick(static function () use ($pool): void {
            self::assertSame([
                Info::TOTAL => 1,
                Info::BUSY => 1,
                Info::CALLS => 0,
                Info::IDLE => 0,
                Info::SIZE => 1,
            ], [...$pool->info()]);
        });

        $money = null;
        try {
            $pool->perform(new WorkObject(Money::EUR(512)));
        } catch (ThrownWork $thrownWork) {
            $money = $thrownWork->work();
        } finally {
            $pool->close();
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
        ], [...$pool->info()]);
    }

    /** @test */
    public function close(): void
    {
        $parallelFactory = new ParallelFactory();

        $group         = Group::create();
        $workerFactory = new ThrowingReturnWorkerFactory();
        $pool          = $this->prophesize(LowLevelPoolInterface::class);
        $pool->acquireGroup()->shouldBeCalled()->willReturn($group);
        $pool->releaseGroup($group)->shouldBeCalled();
        $pool->close()->shouldBeCalled();

        $poolProperty = (new ReflectionClass($parallelFactory))->getProperty('infinitePool');
        $poolProperty->setAccessible(true);
        $poolProperty->setValue($parallelFactory, $pool->reveal());

        $workerPool = new Worker($parallelFactory, $workerFactory, 1);
        $workerPool->close();

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 0,
            Info::SIZE => 0,
        ], [...$workerPool->info()]);
    }

    /** @test */
    public function kill(): void
    {
        $parallelFactory = new ParallelFactory();

        $group         = Group::create();
        $workerFactory = new ThrowingReturnWorkerFactory();
        $pool          = $this->prophesize(LowLevelPoolInterface::class);
        $pool->acquireGroup()->shouldBeCalled()->willReturn($group);
        $pool->releaseGroup($group)->shouldBeCalled();
        $pool->kill()->shouldBeCalled();

        $poolProperty = (new ReflectionClass($parallelFactory))->getProperty('infinitePool');
        $poolProperty->setAccessible(true);
        $poolProperty->setValue($parallelFactory, $pool->reveal());

        $workerPool = new Worker($parallelFactory, $workerFactory, 1);
        $workerPool->kill();

        self::assertSame([
            Info::TOTAL => 0,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 0,
            Info::SIZE => 0,
        ], [...$workerPool->info()]);
    }
}
