<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

use Syde\Vendor\Psr\Http\Message\UriFactoryInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
use RuntimeException;
/**
 * A Merchant.
 */
class Merchant implements MerchantInterface
{
    /** @var positive-int|null */
    protected $id;
    /** @var string */
    protected $environment;
    /** @var string */
    protected $code;
    /** @var string */
    protected $token;
    /** @var UriInterface */
    protected $baseUrl;
    /** @var string */
    protected $transactionUrlTemplate;
    /** @var string */
    protected $label;
    /** @var UriFactoryInterface */
    protected $uriFactory;
    /** @var string */
    private $division;
    /**
     * @param positive-int|null $id
     * @param string $environment
     * @param string $code
     * @param string $token
     * @param string $division
     * @param UriInterface|string $baseUrl
     * @param string $transactionUrlTemplate
     * @param string $label
     * @param UriFactoryInterface $uriFactory
     */
    public function __construct(UriFactoryInterface $uriFactory, ?int $id, string $environment = '', string $code = '', string $token = '', $baseUrl = '', string $transactionUrlTemplate = '', string $label = '', string $division = '')
    {
        $this->uriFactory = $uriFactory;
        $this->id = $id;
        $this->environment = $environment;
        $this->code = $code;
        $this->token = $token;
        $this->baseUrl = $this->normalizeUrl($baseUrl);
        $this->transactionUrlTemplate = $transactionUrlTemplate;
        $this->label = $label;
        $this->division = $division;
    }
    /**
     * @inheritDoc
     */
    public function getId() : ?int
    {
        return $this->id;
    }
    /**
     * @inheritDoc
     */
    public function withId(?int $id) : MerchantInterface
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }
    /**
     * @inheritDoc
     */
    public function getCode() : string
    {
        return $this->code;
    }
    /**
     * @inheritdoc
     */
    public function withCode(string $code) : MerchantInterface
    {
        $clone = clone $this;
        $clone->code = $code;
        return $clone;
    }
    /**
     * @inheritDoc
     */
    public function getToken() : string
    {
        return $this->token;
    }
    /**
     * @inheritdoc
     */
    public function withToken(string $token) : MerchantInterface
    {
        $clone = clone $this;
        $clone->token = $token;
        return $clone;
    }
    /**
     * @inheritDoc
     */
    public function getBaseUrl() : UriInterface
    {
        return $this->baseUrl;
    }
    /**
     * @inheritDoc
     */
    public function withBaseUrl($baseUrl) : MerchantInterface
    {
        $clone = clone $this;
        $clone->baseUrl = $this->normalizeUrl($baseUrl);
        return $clone;
    }
    /**
     * @inheritDoc
     */
    public function getTransactionUrlTemplate() : string
    {
        return $this->transactionUrlTemplate;
    }
    /**
     * @inheritdoc
     */
    public function withTransactionUrlTemplate(string $transactionUrlTemplate) : MerchantInterface
    {
        $clone = clone $this;
        $clone->transactionUrlTemplate = $transactionUrlTemplate;
        return $clone;
    }
    /**
     * @inheritDoc
     */
    public function getLabel() : string
    {
        return $this->label;
    }
    /**
     * @inheritdoc
     */
    public function withLabel(string $label) : MerchantInterface
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }
    /**
     * Normalizes a URL.
     *
     * @param UriInterface|string $url The URL to normalize.
     *
     * @return UriInterface The normalized URL.
     * @throws RuntimeException If problem normalizing.
     */
    protected function normalizeUrl($url) : UriInterface
    {
        if (!$url instanceof UriInterface) {
            $url = $this->uriFactory->createUri($url);
        }
        return $url;
    }
    /**
     * @inheritdoc
     */
    public function getDivision() : string
    {
        return $this->division;
    }
    /**
     * @inheritdoc
     */
    public function withDivision(string $division) : MerchantInterface
    {
        $clone = clone $this;
        $clone->division = $division;
        return $clone;
    }
    /**
     * @inheritdoc
     */
    public function getEnvironment() : string
    {
        return $this->environment;
    }
    /**
     * @inheritdoc
     */
    public function withEnvironment(string $environment) : MerchantInterface
    {
        $clone = clone $this;
        $clone->environment = $environment;
        return $clone;
    }
}
