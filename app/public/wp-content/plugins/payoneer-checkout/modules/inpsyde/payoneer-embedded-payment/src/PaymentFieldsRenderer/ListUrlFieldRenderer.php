<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRenderer;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentFieldsRendererInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\ListUrlEnvironmentExtractor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class ListUrlFieldRenderer implements PaymentFieldsRendererInterface
{
    /**
     * @var ListSessionProvider
     */
    protected $listSessionProvider;
    /**
     * @var ListUrlEnvironmentExtractor
     */
    protected $environmentExtractor;
    /**
     * @var string
     */
    protected $listUrlContainerId;
    /**
     * @var string
     */
    protected $listUrlContainerIdAttributeName;
    /**
     * @var string
     */
    protected $listUrlContainerEnvAttributeName;
    public function __construct(ListSessionProvider $listSessionProvider, ListUrlEnvironmentExtractor $environmentExtractor, string $listUrlContainerId, string $listUrlContainerIdAttributeName, string $listUrlContainerEnvAttributeName)
    {
        $this->listSessionProvider = $listSessionProvider;
        $this->environmentExtractor = $environmentExtractor;
        $this->listUrlContainerId = $listUrlContainerId;
        $this->listUrlContainerIdAttributeName = $listUrlContainerIdAttributeName;
        $this->listUrlContainerEnvAttributeName = $listUrlContainerEnvAttributeName;
    }
    protected function getList(): ListInterface
    {
        $context = ListSessionManager::determineContextFromGlobals();
        return $this->listSessionProvider->provide($context);
    }
    protected function getListLongId(): string
    {
        return $this->getList()->getIdentification()->getLongId();
    }
    protected function getListUrl(): string
    {
        return $this->getList()->getLinks()['self'] ?? '';
    }
    public function renderFields(): string
    {
        $listSessionUrl = $this->getListUrl();
        $listSessionLongId = $this->getListLongId();
        $listSessionEnvironment = $this->environmentExtractor->extract($listSessionUrl);
        $listIdContainer = sprintf('<input type="hidden" name="%1$s" id="%1$s" value="%2$s" %3$s="%5$s" %4$s="%6$s">', esc_attr($this->listUrlContainerId), esc_url_raw($listSessionUrl), $this->listUrlContainerIdAttributeName, $this->listUrlContainerEnvAttributeName, esc_attr($listSessionLongId), esc_attr($listSessionEnvironment));
        return $listIdContainer;
    }
}
