<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Work;

/**
 * @template TWork of Work
 */
interface Worker
{
    /**
     * @param TWork $work
     */
    public function perform(Work $work): Result;
}
