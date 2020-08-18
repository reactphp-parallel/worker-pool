<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Work;

interface Result
{
    public function result(): object;
}
