<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work as WorkContract;
use ReactParallel\Pool\Worker\Work\Worker as WorkerInterface;

final class ReturnWorker implements WorkerInterface
{
    public function perform(WorkContract $work): Result
    {
        return new Result($work->work());
    }
}
