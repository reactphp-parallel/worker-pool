<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker;

interface Result
{
    public function result(): object;
}
