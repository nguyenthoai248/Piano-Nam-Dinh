<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use Throwable;
class InteractionException extends CommandException implements InteractionExceptionInterface
{
    /** @var string */
    protected $interactionCode;
    public function __construct(CommandInterface $command, string $interactionCode, string $message, int $code, ?Throwable $inner)
    {
        parent::__construct($command, $message, $code, $inner);
        $this->interactionCode = $interactionCode;
    }
    /**
     * @inheritDoc
     */
    public function getInteractionCode(): string
    {
        return $this->interactionCode;
    }
}
