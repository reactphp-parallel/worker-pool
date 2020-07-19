<?php

use Money\Money;
use React\EventLoop\Factory;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Worker\Work;
use ReactParallel\Pool\Worker\Workers\ReturnWorkerFactory;
use function React\Promise\all;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

$loop = Factory::create();
$eventLoopBridge = new EventLoopBridge($loop);

$workerFactory = new ReturnWorkerFactory();

$pool = new \ReactParallel\Pool\Worker\Worker($loop, $eventLoopBridge, new Infinite($loop, $eventLoopBridge, 13), $workerFactory, 133);
$promises = [];
$i = 0;
$func = function () use (&$promises, &$i, $pool, &$func, $loop) {
    var_export(iterator_to_array($pool->info()));
    echo $i, PHP_EOL;
    $promises[] = $pool->perform(new Work(Money::EUR($i)));
    echo $i, PHP_EOL;
    if ($i === 512) {
        all($promises)->then(function (array $monies) use ($pool): void {
            var_export(array_map(fn (Money $money) => $money->getAmount(), $monies));
            $pool->close();
        })->done();
    }  else {
        $i++;
        $loop->futureTick($func);
    }
};

$loop->futureTick($func);

$loop->run();
