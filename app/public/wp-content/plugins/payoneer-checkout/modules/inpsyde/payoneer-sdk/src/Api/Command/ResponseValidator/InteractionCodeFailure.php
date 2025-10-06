<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator;

use Syde\Vendor\Psr\Http\Message\ResponseInterface;
use Throwable;
/**
 * A validation failure.
 */
class InteractionCodeFailure extends ValidationFailure implements InteractionCodeFailureInterface
{
    /** @var string */
    protected $interactionCode;
    /**
     * @param ResponseInterface $subject
     * @param ResponseValidatorInterface $validator
     * @param string $message
     * @param ?Throwable $previous
     */
    public function __construct(string $interactionCode, ResponseInterface $subject, ResponseValidatorInterface $validator, string $message = '', Throwable $previous = null)
    {
        parent::__construct($subject, $validator, $message, $previous);
        $this->interactionCode = $interactionCode;
    }
    /**
     * @inheritDoc
     */
    public function getInteractionCode(): string
    {
        return $this->interactionCode;
    }
}
