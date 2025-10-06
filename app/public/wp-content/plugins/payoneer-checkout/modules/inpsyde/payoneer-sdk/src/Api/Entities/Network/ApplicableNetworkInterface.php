<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

/**
 * Not all properties from the ApplicableNetwork API entity have been added.
 * Particularly the ones that only make sense in a front-end context have been omitted.
 */
interface ApplicableNetworkInterface
{
    /**
     * Payment network code.
     */
    public function getCode(): string;
    /**
     * Display label of the payment network.
     */
    public function getLabel(): string;
    /**
     * Payment method
     *
     * @return "BANK_TRANSFER"|"BILLING_PROVIDER"|"CASH_ON_DELIVERY"|"CHECK_PAYMENT"|"CREDIT_CARD"|"DEBIT_CARD"|"DIRECT_DEBIT"|"ELECTRONIC_INVOICE"|"GIFT_CARD"|"MOBILE_PAYMENT"|"ONLINE_BANK_TRANSFER"|"OPEN_INVOICE"|"PREPAID_CARD"|"TERMINAL"|"WALLET"
     */
    public function getMethod(): string;
    /**
     * Grouping code.
     * Helps to group several payment networks together while displaying them on payment page (e.g. credit cards).
     */
    public function getGrouping(): string;
    /**
     * Indicates whether this payment network supports registration and how this should be presented on payment page.
     *
     * @return "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED"
     */
    public function getRegistration(): string;
    /**
     * Indicates whether this payment network supports recurring registration and how this should be presented on payment page.
     *
     * @return "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED"
     */
    public function getRecurrence(): string;
    /**
     * Types of possible operations
     *
     * @return "CHARGE"|"PRESET"|"PAYOUT"|"UPDATE"|"ACTIVATION"
     */
    public function getOperationType(): string;
    /**
     * @return list<string>
     */
    public function getProviders(): array;
    /**
     * Collection of links to build the account form for this payment network and perform different actions with entered account.
     *
     * @return array{
     *     operation?: string,
     *     validation?: string,
     * }
     */
    public function getLinks(): array;
    /**
     * The deferred behavior of the payment network.
     *
     * @return null|"DEFERRED"|"NON_DEFERRED"
     */
    public function getDeferral(): ?string;
}
