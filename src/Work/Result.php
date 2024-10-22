<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Work;

/** @template T */
interface Result
{
    /** @return object<T> */
    public function result(): object;
}
