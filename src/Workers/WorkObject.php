<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\Work;

final class WorkObject implements Work
{
    private object $work;

    public function __construct(object $work)
    {
        $this->work = $work;
    }

    public function work(): object
    {
        return $this->work;
    }
}
