<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\Worker as WorkerInterface;
use ReactParallel\Pool\Worker\Work\WorkerFactory;

final class ReturnWorkerFactory implements WorkerFactory
{
    public function construct(): WorkerInterface
    {
        return new ReturnWorker();
    }
}
