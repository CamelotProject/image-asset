<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Util\Uuid;
use Closure;
use Psr\Log\LoggerInterface;
use function get_class;
use function gettype;
use function is_object;

final class Transaction
{
    public const PASS = 1;
    public const FAIL = 2;

    /** @var string */
    private $id;
    /** @var Closure */
    private $lazyJob;
    /** @var JobInterface|PhaseInterface|RequisitionInterface */
    private $current;
    /** @var LoggerInterface */
    private $logger;
    /** @var ?int */
    private $result = null;

    /** @param callable $lazyJob Callable matching the signature: fn(Transaction $transaction, ...$args): JobInterface */
    public function __construct(RequisitionInterface $requisition, callable $lazyJob, LoggerInterface $logger)
    {
        $this->id = (string) Uuid::uuid4();
        $this->current = $requisition;
        $this->lazyJob = Closure::fromCallable($lazyJob);
        $this->logger = $logger;
    }

    /**
     * Not meant to used for anything requiring true uniqueness/randomness/cryptography/secure/etc.
     *
     * @internal
     */
    public function getId(): string
    {
        return $this->id;
    }

    /** @return JobInterface|RequisitionInterface */
    public function getCurrent(): PhaseInterface
    {
        return $this->current;
    }

    public function start(): ?JobInterface
    {
        $this->doStart();
        $this->logger->debug(sprintf('[%s] Starting image transaction on %s', $this->id, $this->current->getRequestPath()));

        return $this->current;
    }

    public function isComplete(): bool
    {
        return $this->result !== null && $this->result > 0;
    }

    public function isPass(): bool
    {
        return $this->result === self::PASS;
    }

    public function isFail(): bool
    {
        return $this->result === self::FAIL;
    }

    public function setResult(int $result): self
    {
        if ($result !== self::PASS && $result !== self::FAIL) {
            throw new BadMethodCallException(sprintf('Parameter 1 passed to %s must be either Transaction::PASS or Transaction::FAIL. %s given', __CLASS__, $result));
        }
        $this->result = $result;

        return $this;
    }

    private function doStart(...$args): void
    {
        if ($this->current instanceof JobInterface) {
            throw new RuntimeException('Cannot start an already started transaction.');
        }
        $action = ($this->lazyJob)($this, ...$args);
        if (!$action instanceof JobInterface) {
            throw new RuntimeException(sprintf('Callables used to construct jobs must return objects implementing %s, %s given.', JobInterface::class, is_object($action) ? get_class($action) : gettype($action)));
        }
        $this->current = $action;
        $this->result = 0;
        $this->lazyJob = null;
    }
}
