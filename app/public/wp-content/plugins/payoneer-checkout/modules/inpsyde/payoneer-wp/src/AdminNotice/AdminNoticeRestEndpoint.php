<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller\WpRestApiControllerInterface;
use WP_REST_Request;
class AdminNoticeRestEndpoint
{
    private WpRestApiControllerInterface $endpointController;
    private string $namespace;
    private string $route;
    private string $capability;
    private array $methods;
    public function __construct(WpRestApiControllerInterface $endpointController, string $namespace, string $route, string $capability = '', ?array $methods = null)
    {
        $this->endpointController = $endpointController;
        $this->namespace = $namespace;
        $this->route = $route;
        $this->capability = $capability;
        $this->methods = $methods ?: ['POST'];
    }
    public function registerRoute(): void
    {
        $callback = fn(WP_REST_Request $request) => $this->endpointController->handleWpRestRequest($request);
        $permissionCallback = fn() => is_callable([$this->endpointController, 'checkPermission']) && $this->endpointController->checkPermission($this->capability);
        register_rest_route($this->namespace, $this->route, ['methods' => $this->methods, 'callback' => $callback, 'permission_callback' => $permissionCallback]);
    }
    public function endpointPath(): string
    {
        return $this->namespace . $this->route;
    }
}
