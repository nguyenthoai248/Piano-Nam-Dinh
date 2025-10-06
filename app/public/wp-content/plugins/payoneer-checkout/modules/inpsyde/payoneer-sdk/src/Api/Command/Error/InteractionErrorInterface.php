<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Error;

use RuntimeException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\InteractionExceptionInterface;
/**
 * A builder of interaction exceptions.
 *
 * @template E of InteractionExceptionInterface
 * @extends CommandErrorInterface<E>
 */
interface InteractionErrorInterface extends CommandErrorInterface
{
    /**
     * Configures a new instance with the specified interaction code.
     *
     * @param string $code The interaction code.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function withInteractionCode(string $code): self;
}
