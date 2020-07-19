<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Thread;

use Closure;
use parallel\Channel;
use parallel\Channel\Error\Closed;
use ReactParallel\Pool\Worker\Error;
use ReactParallel\Pool\Worker\Message\Error as ErrorMessage;
use ReactParallel\Pool\Worker\Message\Result as ResultMessage;
use ReactParallel\Pool\Worker\Message\Work as WorkMessage;
use ReactParallel\Pool\Worker\Work\WorkerFactory;
use Throwable;

use function assert;

final class Performer
{
    public static function create(): Closure
    {
        return static function (Channel $in, Channel $out, WorkerFactory $workerFactory): void {
            $worker = $workerFactory->construct();
            try {
                while ($work = $in->recv()) {
                    assert($work instanceof WorkMessage);
                    try {
                        /** @psalm-suppress UndefinedDocblockClass */
                        $out->send(new ResultMessage($work->id(), $worker->perform($work->work())));
                    } catch (Throwable $throwable) {
                        $out->send(new ErrorMessage($work->id(), new Error($throwable)));
                    }
                }
            } catch (Closed $closed) {
                // @ignoreException
            }
        };
    }
}
