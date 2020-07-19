<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker;

final class Work
{
    private object $result;

    public function __construct(object $result)
    {
        $this->result = $result;
    }

    public function work(): object
    {
        return $this->result;
    }
}
