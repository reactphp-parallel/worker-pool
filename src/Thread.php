<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker;

use parallel\Channel;
use React\Promise\Deferred;
use ReactParallel\Contracts\PoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Worker\Message\NegativeOutcome;
use ReactParallel\Pool\Worker\Message\Outcome;
use ReactParallel\Pool\Worker\Message\PositiveOutcome;
use ReactParallel\Pool\Worker\Message\Work as WorkMessage;
use ReactParallel\Pool\Worker\Thread\Performer;
use ReactParallel\Pool\Worker\Work\Message;
use ReactParallel\Pool\Worker\Work\Result;
use ReactParallel\Pool\Worker\Work\Work;
use ReactParallel\Pool\Worker\Work\WorkerFactory;

use function count;
use function React\Async\await;
use function spl_object_id;

use const WyriHaximus\Constants\Boolean\FALSE_;
use const WyriHaximus\Constants\Boolean\TRUE_;
use const WyriHaximus\Constants\Numeric\ZERO;

/**
 * @template I
 * @template O
 */
final class Thread
{
    /** @var array<int, Deferred<Outcome>> */
    private array $deferreds = [];
    private bool $closed     = FALSE_;

    /**
     * @param Channel<WorkMessage<I>> $in
     * @param Channel<Result<O>       &Message> $out
     */
    private function __construct(private Channel $in, private Channel $out, EventLoopBridge $eventLoopBridge)
    {
        foreach ($eventLoopBridge->observe($out) as $message) {
            $deferred = $this->deferreds[$message->id()];
            unset($this->deferreds[$message->id()]);

            if ($this->closed === TRUE_ && count($this->deferreds) === ZERO) {
                $this->out->close();
            }

            if ($message instanceof NegativeOutcome) {
                $deferred->reject($message->error()->error());

                return;
            }

            if ($message instanceof PositiveOutcome) {
                $deferred->resolve($message->result()->result());

                return;
            }
        }
    }

    /**
     * @param WorkerFactory<I> $workerFactory
     *
     * @return Thread<O>
     */
    public static function create(WorkerFactory $workerFactory, EventLoopBridge $eventLoopBridge, PoolInterface $pool): self
    {
        $in  = new Channel(Channel::Infinite);
        $out = new Channel(Channel::Infinite);

        /** @psalm-suppress UndefinedInterfaceMethod */
        $pool->run(Performer::create(), [$in, $out, $workerFactory])->done();

        return new self($in, $out, $eventLoopBridge);
    }

    public function perform(Work $work): Result
    {
        $id                   = spl_object_id($work);
        $this->deferreds[$id] = new Deferred();
        $this->in->send(new WorkMessage($id, $work));

        return await($this->deferreds[$id]->promise());
    }

    public function close(): void
    {
        $this->in->close();
        $this->closed = TRUE_;
        if (count($this->deferreds) !== ZERO) {
            return;
        }

        $this->out->close();
    }
}
