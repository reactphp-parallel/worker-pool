<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Work\Message;
use ReactParallel\Pool\Worker\Work\Work as UnitOfWork;

use function assert;
use function serialize;
use function unserialize;

final class Work implements Message
{
    private string $work;

    public function __construct(private int $id, UnitOfWork $work)
    {
        $this->work = serialize($work);
    }

    public function id(): int
    {
        return $this->id;
    }

    public function work(): UnitOfWork
    {
        $work = unserialize($this->work);
        assert($work instanceof UnitOfWork);

        return $work;
    }
}
