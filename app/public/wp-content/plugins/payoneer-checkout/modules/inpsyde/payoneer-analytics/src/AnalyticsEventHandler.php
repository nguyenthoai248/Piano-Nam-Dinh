<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics\AnalyticsApiClient\AnalyticsApiClientInterface;
class AnalyticsEventHandler implements AnalyticsEventHandlerInterface
{
    /**
     * @var AnalyticsApiClientInterface
     */
    protected $apiClient;
    /**
     * @var array<string, string>
     */
    protected $baseContext;
    /**
     * @param AnalyticsApiClientInterface $apiClient
     * @param array<string, string> $baseContext
     */
    public function __construct(AnalyticsApiClientInterface $apiClient, array $baseContext)
    {
        $this->apiClient = $apiClient;
        $this->baseContext = $baseContext;
    }
    /**
     * @inheritDoc
     */
    public function handleAnalyticsEvent(array $trackedHookConfig, array $context): void
    {
        $context = array_merge($this->baseContext, $context);
        $context = $this->processContext($context);
        $this->replacePlaceholders($trackedHookConfig, $context);
        $encodedPayload = json_encode($trackedHookConfig);
        if (!$encodedPayload || json_last_error() !== \JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('Failed to serialize payload data: %1$s', print_r($trackedHookConfig, \true)));
        }
        $this->apiClient->post($encodedPayload);
    }
    /**
     * Execute callable elements in context.
     *
     * @param array $context
     *
     * @return array
     */
    public function processContext(array $context): array
    {
        array_walk($context, static function (&$element): void {
            if (is_callable($element)) {
                $element = $element();
            }
        });
        return $context;
    }
    /**
     * Recursively replace values in trackedHookConfig using context.
     *
     * @param array $trackedHookConfig
     * @param array $context
     */
    public function replacePlaceholders(array &$trackedHookConfig, array $context): void
    {
        foreach ($trackedHookConfig as &$value) {
            if (is_array($value)) {
                $this->replacePlaceholders($value, $context);
                continue;
            }
            if (!is_string($value)) {
                continue;
            }
            if (!stristr($value, '{')) {
                continue;
            }
            $cleanValue = str_replace(['{', '}'], '', $value);
            if (array_key_exists($cleanValue, $context)) {
                $replacement = $context[$cleanValue];
                $value = $replacement;
            }
        }
    }
}
