<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback;

use WC_Payment_Gateway;
class LiveModeAvailabilityCallback implements AvailabilityCallbackInterface
{
    protected bool $isLiveMode;
    protected string $adminPermission;
    protected bool $allowInSandbox;
    public function __construct(bool $isLiveMode, string $adminPermission, bool $allowInSandbox)
    {
        $this->isLiveMode = $isLiveMode;
        $this->adminPermission = $adminPermission;
        $this->allowInSandbox = $allowInSandbox;
    }
    public function __invoke(WC_Payment_Gateway $gateway): bool
    {
        return $this->isLiveMode || current_user_can($this->adminPermission) || $this->allowInSandbox;
    }
}
