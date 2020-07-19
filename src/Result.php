<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker;

final class Result
{
    private object $result;

    public function __construct(object $result)
    {
        $this->result = $result;
    }

    public function result(): object
    {
        return $this->result;
    }
}
