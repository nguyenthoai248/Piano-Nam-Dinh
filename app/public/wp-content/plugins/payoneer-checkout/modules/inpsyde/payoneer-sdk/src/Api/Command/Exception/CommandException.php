<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use RuntimeException;
use Throwable;
class CommandException extends RuntimeException implements CommandExceptionInterface
{
    /** @var CommandInterface */
    protected $command;
    public function __construct(CommandInterface $command, string $message, int $code, ?Throwable $inner)
    {
        parent::__construct($message, $code, $inner);
        $this->command = $command;
    }
    /**
     * @inheritDoc
     */
    public function getCommand(): CommandInterface
    {
        return $this->command;
    }
}
