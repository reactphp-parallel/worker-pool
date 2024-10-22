<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Error as UnitOfError;

use function assert;
use function serialize;
use function unserialize;

final class Error implements NegativeOutcome
{
    private string $error;

    public function __construct(private int $id, UnitOfError $error)
    {
        $this->error = serialize($error);
    }

    public function id(): int
    {
        return $this->id;
    }

    public function error(): UnitOfError
    {
        $error = unserialize($this->error);
        assert($error instanceof UnitOfError);

        return $error;
    }
}
