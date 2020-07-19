<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Result as UnitOfResult;

interface PositiveOutcome extends Outcome
{
    public function result(): UnitOfResult;
}
