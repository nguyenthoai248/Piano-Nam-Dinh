<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiCallExceptionInterface;
use Syde\Vendor\Psr\Http\Message\RequestInterface;
use Syde\Vendor\Psr\Http\Message\ResponseInterface;
class CommandExecutionException extends CommandException implements CommandExecutionExceptionInterface
{
    protected ApiCallExceptionInterface $inner;
    public function __construct(CommandInterface $command, string $message, int $code, ApiCallExceptionInterface $inner)
    {
        parent::__construct($command, $message, $code, $inner);
        $this->inner = $inner;
    }
    public function getRequest(): RequestInterface
    {
        return $this->inner->getRequest();
    }
    public function getResponse(): ?ResponseInterface
    {
        return $this->inner->getResponse();
    }
}
