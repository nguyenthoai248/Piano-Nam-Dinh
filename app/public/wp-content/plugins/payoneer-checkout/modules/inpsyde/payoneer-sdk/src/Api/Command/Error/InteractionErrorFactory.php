<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Error;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\InteractionExceptionInterface;
/**
 * @template E of InteractionExceptionInterface
 * @implements InteractionErrorFactoryInterface<E>
 */
class InteractionErrorFactory implements InteractionErrorFactoryInterface
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
    public function createInteractionError(string $code, string $message): InteractionErrorInterface
    {
        /** @var InteractionErrorInterface<E> */
        $error = (new InteractionError())->withExceptionClass($this->exceptionClass)->withInteractionCode($code)->withMessage($message);
        return $error;
    }
}
