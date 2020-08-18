<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\WorkerFactory;

/**
 * @implements WorkerFactory<WorkObject, ThrowingReturnWorker>
 */
final class ThrowingReturnWorkerFactory implements WorkerFactory
{
    /**
     * @psalm-suppress MismatchingDocblockReturnType
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress UndefinedDocblockClass
     */
    public function construct(): ThrowingReturnWorker
    {
        return new ThrowingReturnWorker();
    }
}
