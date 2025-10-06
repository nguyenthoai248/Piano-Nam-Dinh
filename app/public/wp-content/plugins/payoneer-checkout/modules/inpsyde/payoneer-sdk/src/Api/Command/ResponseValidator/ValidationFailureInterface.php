<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator;

use Syde\Vendor\Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;
/**
 * A validation failure.
 */
interface ValidationFailureInterface extends Throwable
{
    /**
     * Retrieves the subject that failed validation.
     *
     * @return ResponseInterface The subject.
     *
     * @throws RuntimeException if problem retrieving.
     */
    public function getSubject(): ResponseInterface;
}
