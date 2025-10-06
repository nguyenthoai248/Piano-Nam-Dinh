<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class ListSessionManagerProxy implements ListSessionProvider
{
    /**
     * @var callable():ListSessionManager
     */
    private $factory;
    /**
     * @param callable():ListSessionManager $factory
     */
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }
    private function ensureManager(): ListSessionManager
    {
        static $manager;
        if (!$manager) {
            $manager = ($this->factory)();
        }
        assert($manager instanceof ListSessionManager);
        return $manager;
    }
    public function provide(ContextInterface $context): ListInterface
    {
        return $this->ensureManager()->provide($context);
    }
}
