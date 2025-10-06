<?php

namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

/**
 * A command sending API requests for an existing LIST session
 */
interface ListAwareCommandInterface extends CommandInterface
{
    /**
     * @param string $longId
     *
     * @return static
     */
    public function withLongId(string $longId): self;
    /**
     * Get currently configured longId.
     *
     * @return string|null
     */
    public function getLongId(): ?string;
}
