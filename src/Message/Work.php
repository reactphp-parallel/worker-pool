<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Message;

use ReactParallel\Pool\Worker\Message;
use ReactParallel\Pool\Worker\Work as UnitOfWork;

final class Work implements Message
{
    private string $id;
    private UnitOfWork $work;

    public function __construct(string $id, UnitOfWork $work)
    {
        $this->id   = $id;
        $this->work = $work;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function work(): UnitOfWork
    {
        return $this->work;
    }
}
