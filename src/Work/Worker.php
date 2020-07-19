<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Work;

use ReactParallel\Pool\Worker\Result;
use ReactParallel\Pool\Worker\Work;

interface Worker
{
    public function perform(Work $work): Result;
}
