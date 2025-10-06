<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Error;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Error\AbstractError;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Error\ExceptionClassErrorInterface;
use RuntimeException;
use Throwable;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\InteractionExceptionInterface;
/**
 * An interaction error.
 *
 * @template X of InteractionExceptionInterface
 * @implements InteractionErrorInterface<X>
 * @implements ExceptionClassErrorInterface<X>
 */
class InteractionError extends AbstractError implements InteractionErrorInterface, ExceptionClassErrorInterface
{
    /** @var ?string */
    protected $interactionCode;
    /** @var ?class-string<X> */
    protected $exceptionClass;
    /** @var ?CommandInterface */
    protected $command;
    /**
     * @inheritDoc
     */
    public function createException(): Throwable
    {
        if (!$interactionCode = $this->interactionCode) {
            throw new RuntimeException('Interaction Code must be specified');
        }
        if (!$type = $this->exceptionClass) {
            throw new RuntimeException('Exception class must be specified');
        }
        if (!$command = $this->command) {
            throw new RuntimeException('Command must be specified');
        }
        $message = $this->message;
        $code = $this->code;
        $innerException = $this->innerException;
        $exception = new $type($command, $interactionCode, $message, $code, $innerException);
        return $exception;
    }
    /**
     * Configures a new instance with the specified command.
     *
     * @param CommandInterface $command The command.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function withCommand(CommandInterface $command): CommandErrorInterface
    {
        $clone = clone $this;
        $clone->command = $command;
        return $clone;
    }
    /**
     * Configures a new instance with the specified exception FQCN.
     *
     * @param class-string<X> $fqcn The exception FQCN.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function withExceptionClass(string $fqcn): ExceptionClassErrorInterface
    {
        $clone = clone $this;
        $clone->exceptionClass = $fqcn;
        return $clone;
    }
    /**
     * @see InteractionErrorInterface::withInteractionCode()
     *
     * @return static
     */
    public function withInteractionCode(string $code): InteractionErrorInterface
    {
        $clone = clone $this;
        $clone->interactionCode = $code;
        return $clone;
    }
}
