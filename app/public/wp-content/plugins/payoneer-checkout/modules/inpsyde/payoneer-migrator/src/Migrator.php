<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Migration;

use WC_Payment_Gateway;
/**
 * Migrates pre-3.0.0 to 3.0.0.
 *
 * There was more complicated logic for managing multi-steps update if we need to move through
 * a few versions. It was considered unneeded so far and moved to the separate branch so
 * that we can use it when and if we need it. This is the branch
 * https://github.com/inpsyde/payoneer-for-woocommerce/tree/feature/advanced-migrator and related
 * discussion https://github.com/inpsyde/payoneer-for-woocommerce/pull/239#discussion_r1005679309.
 */
class Migrator implements MigratorInterface
{
    /**
     * @inheritDoc
     */
    public function migrate(): void
    {
        $gateways = wc()->payment_gateways()->payment_gateways();
        $cardGateway = $gateways['payoneer-checkout'] ?? null;
        $hostedGateway = $gateways['payoneer-hosted'] ?? null;
        if (!$cardGateway instanceof WC_Payment_Gateway) {
            return;
        }
        $title = $cardGateway->get_option('title');
        $description = $cardGateway->get_option('description');
        if (!$hostedGateway instanceof WC_Payment_Gateway) {
            return;
        }
        if (!empty($title)) {
            $hostedGateway->update_option('title-payoneer-hosted', $title);
            $hostedGateway->update_option('title-payoneer-checkout', $title);
        }
        if (!empty($description)) {
            $hostedGateway->update_option('description-payoneer-hosted', $description);
            $hostedGateway->update_option('description-payoneer-checkout', $description);
        }
    }
}
