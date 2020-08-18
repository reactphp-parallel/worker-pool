<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Work;

/**
 * @template TWork of Work
 * @template TWorker of Worker
 */
interface WorkerFactory
{
    /**
     * @return TWorker<TWork>
     *
     * @psalm-suppress MismatchingDocblockReturnType
     * @psalm-suppress UndefinedDocblockClass
     */
    public function construct(): Worker;
}
