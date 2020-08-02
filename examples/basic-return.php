<?php

use Money\Money;
use React\EventLoop\Factory;
use ReactParallel\Factory as ParallelFactory;
use ReactParallel\Pool\Worker\Workers\ReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\Work;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

$loop = Factory::create();
$parallelFactory = new ParallelFactory($loop);
$workerFactory = new ReturnWorkerFactory();

$pool = new \ReactParallel\Pool\Worker\Worker($parallelFactory, $workerFactory, 133);
$pool->perform(new Work(Money::EUR(512)))->always(function () use ($pool) {
    $pool->close();
})->done(function (Money $money) {
    echo $money->getAmount();
});

$loop->run();
