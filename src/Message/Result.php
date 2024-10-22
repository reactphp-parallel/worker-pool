<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Work\Result as UnitOfResult;

use function assert;
use function serialize;
use function unserialize;

/**
 * @template T
 * @template-implements PositiveOutcome<T>
 */
final class Result implements PositiveOutcome
{
    private string $result;

    /** @param UnitOfResult<T> $result */
    public function __construct(private int $id, UnitOfResult $result)
    {
        $this->result = serialize($result);
    }

    public function id(): int
    {
        return $this->id;
    }

    /** @return UnitOfResult<T> */
    public function result(): UnitOfResult
    {
        $result = unserialize($this->result);
        assert($result instanceof UnitOfResult);

        return $result;
    }
}
