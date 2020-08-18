<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Work\Message;
use ReactParallel\Pool\Worker\Work\Work as UnitOfWork;

use function serialize;
use function unserialize;

final class Work implements Message
{
    private string $id;
    private string $work;

    public function __construct(string $id, UnitOfWork $work)
    {
        $this->id   = $id;
        $this->work = serialize($work);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function work(): UnitOfWork
    {
        return unserialize($this->work);
    }
}
