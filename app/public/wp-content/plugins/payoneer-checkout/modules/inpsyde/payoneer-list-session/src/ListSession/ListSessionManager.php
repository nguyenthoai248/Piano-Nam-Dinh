<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class ListSessionManager implements ListSessionProvider
{
    /**
     * @var ListSessionMiddleware[]|ListSessionProvider[]
     */
    private $middlewares;
    /**
     * @param ListSessionMiddleware[]|ListSessionProvider[] $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }
    public function provide(ContextInterface $context): ListInterface
    {
        reset($this->middlewares);
        $runner = new Runner($this->middlewares);
        return $runner->provide($context);
    }
}
