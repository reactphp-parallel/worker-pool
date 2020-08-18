<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\Work;
use ReactParallel\Pool\Worker\Work\Worker;

/**
 * @implements Worker<WorkObject>
 */
final class ReturnWorker implements Worker
{
    public function perform(Work $work): Result
    {
        return new Result($work->work());
    }
}
