<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Work;

interface WorkerFactory
{
    public function construct(): Worker;
}
