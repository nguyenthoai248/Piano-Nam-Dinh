<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception;

use RuntimeException;
/**
 * A problem signalled by a failure interaction code.
 */
interface InteractionExceptionInterface extends CommandExceptionInterface
{
    /**
     * Retrieves the interaction code provided by Payoneer.
     *
     * @return string The interaction code.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getInteractionCode(): string;
}
