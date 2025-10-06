<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator;

use Syde\Vendor\Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;
/**
 * A validation failure.
 */
class ValidationFailure extends RuntimeException implements ValidationFailureInterface, ValidatorFailureInterface
{
    /** @var ResponseInterface */
    protected $subject;
    /** @var ResponseValidatorInterface */
    protected $validator;
    /**
     * @param ResponseInterface $subject
     * @param ResponseValidatorInterface $validator
     * @param string $message
     * @param ?Throwable $previous
     */
    public function __construct(ResponseInterface $subject, ResponseValidatorInterface $validator, string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->subject = $subject;
        $this->validator = $validator;
    }
    /**
     * @inheritDoc
     */
    public function getSubject(): ResponseInterface
    {
        return $this->subject;
    }
    /**
     * @inheritDoc
     */
    public function getValidator(): ResponseValidatorInterface
    {
        return $this->validator;
    }
}
