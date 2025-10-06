<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class EnvironmentProvider
{
    protected ListUrlEnvironmentExtractor $listUrlEnvironmentExtractor;
    protected ListSessionProvider $listSessionProvider;
    public function __construct(ListUrlEnvironmentExtractor $listUrlEnvironmentExtractor, ListSessionProvider $listSessionProvider)
    {
        $this->listUrlEnvironmentExtractor = $listUrlEnvironmentExtractor;
        $this->listSessionProvider = $listSessionProvider;
    }
    public function provide(): string
    {
        $listUrl = $this->getListUrl();
        return $this->listUrlEnvironmentExtractor->extract($listUrl);
    }
    protected function getList(): ListInterface
    {
        return $this->listSessionProvider->provide(new PaymentContext());
    }
    protected function getListLongId(): string
    {
        return $this->getList()->getIdentification()->getLongId();
    }
    protected function getListUrl(): string
    {
        return $this->getList()->getLinks()['self'] ?? '';
    }
}
