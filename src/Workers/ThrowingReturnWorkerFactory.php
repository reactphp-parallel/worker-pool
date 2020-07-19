<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\Worker as WorkerInterface;
use ReactParallel\Pool\Worker\Work\WorkerFactory;

final class ThrowingReturnWorkerFactory implements WorkerFactory
{
    public function construct(): WorkerInterface
    {
        return new ThrowingReturnWorker();
    }
}
