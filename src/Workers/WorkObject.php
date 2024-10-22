<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\Work;

final class WorkObject implements Work
{
    public function __construct(private object $work)
    {
    }

    public function work(): object
    {
        return $this->work;
    }
}
