<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment;

class UniqidInvoiceIdProvider implements InvoiceIdProviderInterface
{
    public function provide(): string
    {
        return uniqid('inv_', \true);
    }
}
