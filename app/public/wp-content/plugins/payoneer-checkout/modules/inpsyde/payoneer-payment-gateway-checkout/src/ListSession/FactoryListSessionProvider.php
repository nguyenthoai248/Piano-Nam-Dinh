<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

/**
 * Provides a ListInterface by invoking an appropriate factory function
 */
class FactoryListSessionProvider implements ListSessionProvider
{
    /**
     * @var callable():ListInterface
     */
    protected $factory;

    /**
     * @param callable():ListInterface $factory
     */
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function provide(): ListInterface
    {
        try {
            return ($this->factory)();
        } catch (\Throwable $exception) {
            throw new CheckoutException(
                'Failed to invoke List session factory.',
                0,
                $exception
            );
        }
    }
}
