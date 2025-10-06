<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

interface ApplicableNetworkFactoryInterface
{
    /**
     * @param string $code
     * @param string $label
     * @param "BANK_TRANSFER"|"BILLING_PROVIDER"|"CASH_ON_DELIVERY"|"CHECK_PAYMENT"|"CREDIT_CARD"|"DEBIT_CARD"|"DIRECT_DEBIT"|"ELECTRONIC_INVOICE"|"GIFT_CARD"|"MOBILE_PAYMENT"|"ONLINE_BANK_TRANSFER"|"OPEN_INVOICE"|"PREPAID_CARD"|"TERMINAL"|"WALLET" $method
     * @param string $grouping
     * @param "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED" $registration
     * @param "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED" $recurrence
     * @param "CHARGE"|"PRESET"|"PAYOUT"|"UPDATE"|"ACTIVATION" $operationType
     * @param list<string> $providers
     * @param array{
     *      operation?: string,
     *      validation?: string,
     *  } $links
     * @param null|"DEFERRED"|"NON_DEFERRED" $deferral
     */
    public function createApplicableNetwork(string $code, string $label, string $method, string $grouping, string $registration, string $recurrence, string $operationType, array $providers, array $links, string $deferral = null): ApplicableNetworkInterface;
}
