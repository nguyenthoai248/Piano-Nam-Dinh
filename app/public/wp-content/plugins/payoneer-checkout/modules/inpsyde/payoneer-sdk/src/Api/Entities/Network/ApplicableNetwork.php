<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

class ApplicableNetwork implements ApplicableNetworkInterface
{
    /** @var string */
    protected $code;
    /** @var string */
    protected $label;
    /** @var "BANK_TRANSFER"|"BILLING_PROVIDER"|"CASH_ON_DELIVERY"|"CHECK_PAYMENT"|"CREDIT_CARD"|"DEBIT_CARD"|"DIRECT_DEBIT"|"ELECTRONIC_INVOICE"|"GIFT_CARD"|"MOBILE_PAYMENT"|"ONLINE_BANK_TRANSFER"|"OPEN_INVOICE"|"PREPAID_CARD"|"TERMINAL"|"WALLET" */
    protected $method;
    /** @var string */
    protected $grouping;
    /** @var "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED" */
    protected $registration;
    /** @var "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED" */
    protected $recurrence;
    /** @var "CHARGE"|"PRESET"|"PAYOUT"|"UPDATE"|"ACTIVATION" */
    protected $operationType;
    /** @var list<string> */
    protected $providers = [];
    /** @var array */
    protected $links;
    /** @var null|"DEFERRED"|"NON_DEFERRED" */
    protected $deferral;
    /**
     * @param string $code
     * @param string $label
     * @param "BANK_TRANSFER"|"BILLING_PROVIDER"|"CASH_ON_DELIVERY"|"CHECK_PAYMENT"|"CREDIT_CARD"|"DEBIT_CARD"|"DIRECT_DEBIT"|"ELECTRONIC_INVOICE"|"GIFT_CARD"|"MOBILE_PAYMENT"|"ONLINE_BANK_TRANSFER"|"OPEN_INVOICE"|"PREPAID_CARD"|"TERMINAL"|"WALLET" $method
     * @param string $grouping
     * @param "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED" $registration
     * @param "NONE"|"OPTIONAL"|"FORCED"|"OPTIONAL_PRESELECTED" $recurrence
     * @param "CHARGE"|"PRESET"|"PAYOUT"|"UPDATE"|"ACTIVATION" $operationType
     * @param list<string> $providers
     * @param array $links
     * @param null|"DEFERRED"|"NON_DEFERRED" $deferral
     */
    public function __construct(string $code, string $label, string $method, string $grouping, string $registration, string $recurrence, string $operationType, array $providers, array $links, string $deferral = null)
    {
        $this->code = $code;
        $this->label = $label;
        $this->method = $method;
        $this->grouping = $grouping;
        $this->registration = $registration;
        $this->recurrence = $recurrence;
        $this->operationType = $operationType;
        $this->providers = $providers;
        $this->links = $links;
        $this->deferral = $deferral;
    }
    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }
    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }
    /**
     * @inheritDoc
     */
    public function getGrouping(): string
    {
        return $this->grouping;
    }
    /**
     * @inheritDoc
     */
    public function getRegistration(): string
    {
        return $this->registration;
    }
    /**
     * @inheritDoc
     */
    public function getRecurrence(): string
    {
        return $this->recurrence;
    }
    /**
     * @inheritDoc
     */
    public function getOperationType(): string
    {
        return $this->operationType;
    }
    /**
     * @inheritDoc
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
    /**
     * @inheritDoc
     */
    public function getLinks(): array
    {
        return $this->links;
    }
    /**
     * @inheritDoc
     */
    public function getDeferral(): ?string
    {
        return $this->deferral;
    }
}
