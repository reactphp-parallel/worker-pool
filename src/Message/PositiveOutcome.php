<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Work\Result as UnitOfResult;

/** @template T */
interface PositiveOutcome extends Outcome
{
    /** @return UnitOfResult<T> */
    public function result(): UnitOfResult;
}
