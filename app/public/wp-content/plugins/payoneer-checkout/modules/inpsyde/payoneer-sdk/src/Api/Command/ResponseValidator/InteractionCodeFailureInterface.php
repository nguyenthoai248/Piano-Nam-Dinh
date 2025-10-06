<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator;

use RuntimeException;
/**
 * Signifies that the subject's interaction code is invalid.
 */
interface InteractionCodeFailureInterface extends ValidationFailureInterface
{
    /**
     * Retrieves the interaction code.
     *
     * @return string The interaction code.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getInteractionCode(): string;
}
