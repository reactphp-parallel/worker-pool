<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Work;

interface Work
{
    public function work(): object;
}
