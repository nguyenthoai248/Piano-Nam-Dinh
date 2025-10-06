<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Represents appearance settings of a LIST session.
 */
interface StyleInterface
{
    /**
     * Return list session language.
     *
     * Format <language code>[_<COUNTRY CODE>], where <language code> is a mandatory part that comply
     * with ISO 639-1 (alpha-2), and <COUNTRY CODE> is an optional part that comply with ISO 3166-1 (alpha-2).
     * Examples: de - for German, de_CH - for Swiss German.
     *
     *
     * @see https://www.optile.io/reference#operation/createPaymentSession
     *
     * @return string LIST session language.
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getLanguage(): string;
    /**
     * Version of hosted payment page that merchant would prefer to render the LIST session with when using
     * HOSTED integration type. Currently supported versions: v2, v3, v4
     * Warning: not specifying hostedVersion field will result in a deprecated version of the hosted payment page.
     * 3DS2 flow may not be handled properly by this page.
     * For MOBILE_NATIVE integration hostedVersion must NOT be specified.
     *
     * @throws ApiExceptionInterface If this field is not set.
     *
     * @return string
     */
    public function getHostedVersion(): string;
    /**
     * Returns a new Style object with the $hostedVersion applied
     *
     * @param string $hostedVersion
     *
     * @return StyleInterface
     */
    public function withHostedVersion(string $hostedVersion): StyleInterface;
}
