<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class NoopListSessionPersistor implements ListSessionPersistor
{
    public function persist(?ListInterface $list, ContextInterface $context): bool
    {
        return \true;
    }
}
