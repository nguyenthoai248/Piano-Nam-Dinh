<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use RuntimeException;
class Runner implements ListSessionProvider
{
    /**
     * @var array<ListSessionMiddleware|ListSessionProvider>
     */
    private $middlewares;
    /**
     * @param array<ListSessionMiddleware|ListSessionProvider> $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }
    public function provide(ContextInterface $context): ListInterface
    {
        $middleware = current($this->middlewares);
        if ($middleware === \false) {
            throw new RuntimeException('Failed to provide List session, no suitable provider found.');
        }
        next($this->middlewares);
        /**
         * We only process provider middlewares. So if we have a wrong one,
         * skip it by calling the handler again, moving the cursor forward
         */
        if ($middleware instanceof ListSessionMiddleware && !$middleware instanceof ListSessionProviderMiddleware) {
            return $this->provide($context);
        }
        if ($middleware instanceof ListSessionProviderMiddleware) {
            return $middleware->provide($context, $this);
        }
        if ($middleware instanceof ListSessionProvider) {
            return $middleware->provide($context);
        }
        throw new RuntimeException(sprintf('Invalid middleware queue entry: %s. Middleware must either be callable or implement %s.', get_class($middleware), ListSessionProviderMiddleware::class));
    }
}
