<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Pool\Worker\Workers;

use Money\Money;
use ReactParallel\Pool\Worker\Work;
use ReactParallel\Pool\Worker\Workers\ReturnWorkerFactory;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

final class ReturnTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function return(): void
    {
        $money = (new ReturnWorkerFactory())->construct()->perform(new Work(Money::EUR(512)))->result();
        self::assertInstanceOf(Money::class, $money);
        self::assertSame('512', $money->getAmount());
    }
}
