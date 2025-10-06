<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator;

use RuntimeException;
use Throwable;
/**
 * A failure of a validator.
 */
interface ValidatorFailureInterface extends Throwable
{
    /**
     * Retrieves the validator that caused this failure.
     *
     * @return ResponseValidatorInterface The validator.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getValidator(): ResponseValidatorInterface;
}
