<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use Exception;
use ReactParallel\Pool\Worker\Work\Work;
use WyriHaximus\AdditionalPropertiesInterface;

final class ThrownWork extends Exception implements AdditionalPropertiesInterface
{
    /** @psalm-suppress MissingConstructor */
    private object $work;

    public function __construct(Work $work)
    {
        parent::__construct('Throwing work');
        $this->work = $work->work();
    }

    public function work(): object
    {
        return $this->work;
    }

    /**
     * @return array<string>
     */
    public function additionalProperties(): array
    {
        return ['work'];
    }
}
