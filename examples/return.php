<?php

use Money\Money;
use React\EventLoop\Factory;
use ReactParallel\Factory as ParallelFactory;
use ReactParallel\Pool\Worker\Workers\ReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\WorkObject;
use function React\Promise\all;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

$loop = Factory::create();
$parallelFactory = new ParallelFactory($loop);
$workerFactory = new ReturnWorkerFactory();

$pool = new \ReactParallel\Pool\Worker\Worker($parallelFactory, $workerFactory, 133);
$promises = [];
$i = 0;
$func = function () use (&$promises, &$i, $pool, &$func, $loop) {
    var_export(iterator_to_array($pool->info()));
    echo $i, PHP_EOL;
    $promises[] = $pool->perform(new WorkObject(Money::EUR($i)));
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
