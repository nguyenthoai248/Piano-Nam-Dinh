<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Error;

use Throwable;
/**
 * Common exception builder functionality.
 */
abstract class AbstractError implements ErrorInterface
{
    /** @var string */
    protected $message = '';
    /** @var int */
    protected $code = 0;
    /** @var ?Throwable */
    protected $innerException = null;
    /**
     * @inheritDoc
     */
    public function withMessage(string $message): ErrorInterface
    {
        $clone = clone $this;
        $clone->message = $message;
        return $clone;
    }
    /**
     * @inheritDoc
     */
    public function withInnerException(Throwable $exception): ErrorInterface
    {
        $clone = clone $this;
        $clone->innerException = $exception;
        return $clone;
    }
    /**
     * @inheritDoc
     */
    public function withCode(int $code): ErrorInterface
    {
        $clone = clone $this;
        $clone->code = $code;
        return $clone;
    }
}
