<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant;

use InvalidArgumentException;
use Syde\Vendor\Psr\Http\Message\UriFactoryInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
use RangeException;
use RuntimeException;
/**
 * Can create a Merchant.
 */
class MerchantFactory implements MerchantFactoryInterface
{
    /** @var UriFactoryInterface */
    protected $uriFactory;
    public function __construct(UriFactoryInterface $uriFactory)
    {
        $this->uriFactory = $uriFactory;
    }
    /**
     * @inheritDoc
     */
    public function createMerchant(?int $id, string $environment = '', string $code = '', string $token = '', $baseUrl = '', string $transactionUrlTemplate = '', string $label = '', string $division = ''): MerchantInterface
    {
        $product = new Merchant($this->uriFactory, $id, '', '', '', '', '', '');
        $product = $product->withCode($code)->withToken($token)->withBaseUrl($baseUrl)->withTransactionUrlTemplate($transactionUrlTemplate)->withLabel($label)->withDivision($division);
        return $product;
    }
    /**
     * Creates a URL.
     *
     * @param string $url The URL string.
     *
     * @return UriInterface The new URL.
     *
     * @throws RangeException If the given URI cannot be parsed.
     * @throws RuntimeException If problem creating.
     */
    protected function createUrl(string $url): UriInterface
    {
        try {
            return $this->uriFactory->createUri($url);
            // phpcs:ignore Inpsyde.CodeQuality.ElementNameMinimalLength.TooShort
        } catch (InvalidArgumentException $e) {
            throw new RangeException(sprintf('The URL "%1$s" is malformed', $url));
        }
    }
}
