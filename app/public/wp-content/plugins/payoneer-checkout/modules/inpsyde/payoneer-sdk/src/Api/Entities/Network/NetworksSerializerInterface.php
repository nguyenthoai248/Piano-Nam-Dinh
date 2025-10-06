<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
interface NetworksSerializerInterface
{
    /**
     * @return array{
     *      applicable: list<array{
     *          code: string,
     *          label: string,
     *          method: "BANK_TRANSFER"|"BILLING_PROVIDER"|"CASH_ON_DELIVERY"|"CHECK_PAYMENT"|"CREDIT_CARD"|"DEBIT_CARD"|"DIRECT_DEBIT"|"ELECTRONIC_INVOICE"|"GIFT_CARD"|"MOBILE_PAYMENT"|"ONLINE_BANK_TRANSFER"|"OPEN_INVOICE"|"PREPAID_CARD"|"TERMINAL"|"WALLET",
     *          grouping: string,
     *          registration: "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED",
     *          recurrence: "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED",
     *          operationType: "CHARGE"|"PRESET"|"PAYOUT"|"UPDATE"|"ACTIVATION",
     *          providers: list<string>,
     *          links: array{operation?: string, validation?: string},
     *          deferral?: "DEFERRED"|"NON_DEFERRED"|null
     *      }>
     *  }
     *
     * @throws ApiExceptionInterface If something went wrong.
     */
    public function serializeNetworks(NetworksInterface $networks): array;
}
