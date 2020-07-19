<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Pool\Worker\Thread;

use Money\Money;
use parallel\Channel;
use parallel\Runtime;
use ReactParallel\Pool\Worker\Message\Work as WorkMessage;
use ReactParallel\Pool\Worker\Thread\Performer;
use ReactParallel\Pool\Worker\Workers\ReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\ThrowingReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\WorkObject;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

use function dirname;
use function Safe\sleep;

final class PerformerTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function return(): void
    {
        $runtime       = new Runtime(dirname(dirname(__DIR__)) . '/vendor/autoload.php');
        $workerFactory = new ReturnWorkerFactory();
        $in            = new Channel(Channel::Infinite);
        $out           = new Channel(Channel::Infinite);

        $runtime->run(static function (Channel $in): void {
            sleep(1);
            $in->send(new WorkMessage('abc', new WorkObject(Money::EUR(512))));
            sleep(1);
            $in->close();
        }, [$in]);

        Performer::create()($in, $out, $workerFactory);
        $money = $out->recv()->result()->result();
        $out->close();
        $runtime->close();
        self::assertInstanceOf(Money::class, $money);
        self::assertSame('512', $money->getAmount());
    }

    /**
     * @test
     */
    public function throw(): void
    {
        $runtime       = new Runtime(dirname(dirname(__DIR__)) . '/vendor/autoload.php');
        $workerFactory = new ThrowingReturnWorkerFactory();
        $in            = new Channel(Channel::Infinite);
        $out           = new Channel(Channel::Infinite);

        $runtime->run(static function (Channel $in): void {
            sleep(1);
            $in->send(new WorkMessage('abc', new WorkObject(Money::EUR(512))));
            sleep(1);
            $in->close();
        }, [$in]);

        Performer::create()($in, $out, $workerFactory);
        $money = $out->recv()->error()->error()->work();
        $out->close();
        $runtime->close();
        self::assertInstanceOf(Money::class, $money);
        self::assertSame('512', $money->getAmount());
    }
}
