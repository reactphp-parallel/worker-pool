<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Error as UnitOfError;

interface NegativeOutcome extends Outcome
{
    public function error(): UnitOfError;
}
