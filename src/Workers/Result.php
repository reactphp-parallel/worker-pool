<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\Result as ResultContract;

final class Result implements ResultContract
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
