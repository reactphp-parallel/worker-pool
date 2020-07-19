<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Result;
use ReactParallel\Pool\Worker\Work;
use ReactParallel\Pool\Worker\Work\Worker as WorkerInterface;

final class ThrowingReturnWorker implements WorkerInterface
{
    public function perform(Work $work): Result
    {
        throw new ThrownWork($work);
    }
}
