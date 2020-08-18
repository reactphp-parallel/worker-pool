<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Work;

interface Message
{
    public function id(): string;
}
