<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker;

use Throwable;

use function WyriHaximus\throwable_decode;
use function WyriHaximus\throwable_encode;

final class Error
{
    /** @var array{class: class-string<Throwable>, message: string, code: mixed, file: string, line: int, previous: string|null, originalTrace: array<int, mixed>, additionalProperties: array<string, string>} */
    private array $error;

    public function __construct(Throwable $error)
    {
        $this->error = throwable_encode($error);
    }

    public function error(): Throwable
    {
        return throwable_decode($this->error);
    }
}
