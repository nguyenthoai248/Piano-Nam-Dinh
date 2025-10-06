<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator;

use Syde\Vendor\Psr\Http\Message\ResponseInterface;
use RangeException;
use RuntimeException;
/**
 * Validates a response.
 */
interface ResponseValidatorInterface
{
    /**
     * Validates a response.
     *
     * @param ResponseInterface $response The response to validate.
     *
     * @throws ValidatorFailureInterface|RuntimeException If response invalid.
     * @throws ValidationFailureInterface|ValidatorFailureInterface|RangeException If problem validating.
     */
    public function validateResponse(ResponseInterface $response): void;
}
