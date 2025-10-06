<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Error;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use RuntimeException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Error\ErrorInterface;
/**
 * A builder of command exception.
 *
 * @template E of CommandExceptionInterface
 *
 * @extends ErrorInterface<E>
 */
interface CommandErrorInterface extends ErrorInterface
{
    /**
     * Configures a new instance with the specified command.
     *
     * @param CommandInterface $command The command.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function withCommand(CommandInterface $command): self;
}
