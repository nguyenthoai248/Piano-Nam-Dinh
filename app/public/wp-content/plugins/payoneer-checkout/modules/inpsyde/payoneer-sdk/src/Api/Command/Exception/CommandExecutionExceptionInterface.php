<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use Syde\Vendor\Psr\Http\Message\RequestInterface;
use Syde\Vendor\Psr\Http\Message\ResponseInterface;
use RuntimeException;
/**
 * A problem with a command execution.
 */
interface CommandExecutionExceptionInterface extends CommandExceptionInterface
{
    public function getRequest(): RequestInterface;
    public function getResponse(): ?ResponseInterface;
}
