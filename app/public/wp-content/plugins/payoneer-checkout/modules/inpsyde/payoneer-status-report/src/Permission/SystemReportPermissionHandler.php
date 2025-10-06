<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Permission;

use Syde\Vendor\Dhii\Collection\MapInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantInterface;
use WP_Error;
use WP_REST_Request;
/**
 * Encapsulates the authenticating-logic the system-report REST endpoint.
 */
class SystemReportPermissionHandler
{
    /**
     * URL parameter used for authentication.
     * Required, integer.
     */
    private const PARAM_CODE = 'code';
    /**
     * Name of the option field that stores the opt-in flag.
     * Value: ['yes'|'no'|null]
     */
    public const OPTION_FEATURE_OPT_IN_KEY = 'system_report_opt_in';
    /**
     * @var MapInterface
     */
    private MapInterface $options;
    /**
     * @var MerchantInterface
     */
    private MerchantInterface $merchant;
    /**
     * @param MapInterface $options
     * @param MerchantInterface $merchant
     */
    public function __construct(MapInterface $options, MerchantInterface $merchant)
    {
        $this->options = $options;
        $this->merchant = $merchant;
    }
    /**
     * Check permissions and return either true or a WP_REST_Response object.
     *
     * This is the main API of this class and is used as the `permission_callback` for the endpoint.
     *
     * @param WP_REST_Request $request
     *
     * @return bool|WP_Error True if allowed.
     */
    // phpcs:ignore Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType -- cannot declare union types.
    public function checkPermission(WP_REST_Request $request)
    {
        do_action('payoneer-checkout.system-status-report.request-received', ['request' => $request]);
        // Check if the user opted out of this feature.
        if ($this->isFeatureOptedOut()) {
            do_action('payoneer-checkout.system-status-report.request-rejected.opt-out');
            return new WP_Error('rest_disabled', 'Endpoint disabled by site admin.', ['status' => 403]);
        }
        // Verify the store code.
        $storeCode = $this->getStoreCodeFromRequest($request);
        if (\true !== $this->verifyCode($storeCode)) {
            do_action('payoneer-checkout.system-status-report.request-rejected.code-mismatch');
            return new WP_Error('rest_forbidden', 'Authentication failed.', ['status' => 401]);
        }
        return \true;
    }
    /**
     * Check if the feature is opted out.
     *
     * When the setting is missing, the merchant is automatically opted-in, until they explicitly
     * uncheck the setting to opt-out.
     *
     * @return bool True, if opted out, false otherwise.
     */
    private function isFeatureOptedOut(): bool
    {
        return $this->options->has(self::OPTION_FEATURE_OPT_IN_KEY) && 'no' === $this->options->get(self::OPTION_FEATURE_OPT_IN_KEY);
    }
    /**
     * Extract store code from request.
     *
     * @param WP_REST_Request $request
     *
     * @return string The sanitized, cleaned code value.
     */
    private function getStoreCodeFromRequest(WP_REST_Request $request): string
    {
        $cleanCode = sanitize_text_field(trim((string) $request->get_param(self::PARAM_CODE)));
        if ($cleanCode > 0) {
            return $cleanCode;
        }
        return '';
    }
    /**
     * Verify the provided code against the merchant's store code.
     *
     * @param string $providedCode
     *
     * @return bool True, if the provided code matches the expected store code.
     */
    private function verifyCode(string $providedCode): bool
    {
        if (empty($providedCode)) {
            return \false;
        }
        $expectedStoreCode = $this->merchant->getDivision();
        return $providedCode === $expectedStoreCode;
    }
}
