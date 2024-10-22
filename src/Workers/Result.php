<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\Result as ResultContract;

/**
 * @template T
 * @template-implements ResultContract<T>
 */
final class Result implements ResultContract
{
    /** @param object<T> $result */
    public function __construct(private object $result)
    {
    }

    /** @return object<T> */
    public function result(): object
    {
        return $this->result;
    }
}
