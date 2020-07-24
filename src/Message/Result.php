<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Result as UnitOfResult;

final class Result implements PositiveOutcome
{
    private string $id;
    private string $result;

    public function __construct(string $id, UnitOfResult $result)
    {
        $this->id     = $id;
        $this->result = serialize($result);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function result(): UnitOfResult
    {
        return unserialize($this->result);
    }
}
