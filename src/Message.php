<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker;

interface Message
{
    public function id(): string;
}
