<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work as WorkContract;

final class Work implements WorkContract
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
