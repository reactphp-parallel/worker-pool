<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Pool\Worker\Workers;

use Money\Money;
use ReactParallel\Pool\Worker\Workers\ThrowingReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\ThrownWork;
use ReactParallel\Pool\Worker\Workers\Work;
use Throwable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

final class ThrowingReturnTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function thrown(): void
    {
        $money      = null;
        $thrownWork = null;
        try {
            (new ThrowingReturnWorkerFactory())->construct()->perform(new Work(Money::EUR(512)));
        } catch (ThrownWork $thrownWork) {
            $money = $thrownWork->work();
        } catch (Throwable $throwable) {
            throw $throwable;
        }

        self::assertNotNull($thrownWork);
        self::assertSame('Throwing work', $thrownWork->getMessage());
        self::assertNotNull($money);
        self::assertInstanceOf(Money::class, $money);
        self::assertSame('512', $money->getAmount());
    }
}
