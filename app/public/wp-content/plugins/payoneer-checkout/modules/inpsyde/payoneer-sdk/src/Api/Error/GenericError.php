<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Error;

use Exception;
use RuntimeException;
use Throwable;
/**
 * Can build an instance of any exception class whose constructor
 * signature matches that of the {@see Exception} class.
 *
 * @template E of Throwable
 * @implements ErrorInterface<E>
 * @implements ExceptionClassErrorInterface<E>
 */
class GenericError extends AbstractError implements ErrorInterface, ExceptionClassErrorInterface
{
    /** @var class-string<E> */
    protected $exceptionClass;
    /**
     * @param class-string<E> $exceptionClass
     */
    public function __construct(string $exceptionClass)
    {
        $this->exceptionClass = $exceptionClass;
    }
    /**
     * @inheritDoc
     */
    public function createException(): Throwable
    {
        $type = $this->exceptionClass;
        $exception = new $type($this->message, $this->code, $this->innerException);
        return $exception;
    }
    /**
     * Configures a new instance with the specified exception FQCN.
     *
     * @param class-string<E> $fqcn The exception FQCN.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     *
     * @psalm-return static<E>
     */
    public function withExceptionClass(string $fqcn): ExceptionClassErrorInterface
    {
        $clone = clone $this;
        $clone->exceptionClass = $fqcn;
        return $clone;
    }
}
