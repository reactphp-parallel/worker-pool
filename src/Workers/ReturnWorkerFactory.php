<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\WorkerFactory;

/**
 * @implements WorkerFactory<WorkObject, ReturnWorker>
 */
final class ReturnWorkerFactory implements WorkerFactory
{
    /**
     * @psalm-suppress MismatchingDocblockReturnType
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress UndefinedDocblockClass
     */
    public function construct(): ReturnWorker
    {
        return new ReturnWorker();
    }
}
