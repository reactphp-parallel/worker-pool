<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Work;

/**
 * @template TResult
 * @template TWork of Work
 */
interface Worker
{
    /**
     * @param TWork $work
     *
     * @return Result<TResult>
     */
    public function perform(Work $work): Result;
}
