<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Error as UnitOfError;

final class Error implements NegativeOutcome
{
    private string $id;
    private UnitOfError $error;

    public function __construct(string $id, UnitOfError $error)
    {
        $this->id    = $id;
        $this->error = $error;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function error(): UnitOfError
    {
        return $this->error;
    }
}
