<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant;

use Syde\Vendor\Psr\Http\Message\UriInterface;
/**
 * @psalm-suppress LessSpecificReturnStatement
 * @psalm-suppress MoreSpecificReturnType
 */
abstract class AbstractMerchantDecorator implements MerchantInterface
{
    /**
     * @var MerchantInterface
     */
    protected $merchant;
    public function __construct(MerchantInterface $merchant)
    {
        $this->merchant = $merchant;
    }
    public function getId(): ?int
    {
        return $this->merchant->getId();
    }
    public function withId(?int $id): MerchantInterface
    {
        return $this->createClone($this->merchant->withId($id));
    }
    public function getLabel(): string
    {
        return $this->merchant->getLabel();
    }
    public function withLabel(string $label): MerchantInterface
    {
        return $this->createClone($this->merchant->withLabel($label));
    }
    public function getEnvironment(): string
    {
        return $this->merchant->getEnvironment();
    }
    public function withEnvironment(string $environment): MerchantInterface
    {
        return $this->createClone($this->merchant->withEnvironment($environment));
    }
    public function getCode(): string
    {
        return $this->merchant->getCode();
    }
    public function withCode(string $code): MerchantInterface
    {
        return $this->createClone($this->merchant->withCode($code));
    }
    public function getDivision(): string
    {
        return $this->merchant->getDivision();
    }
    public function withDivision(string $division): MerchantInterface
    {
        return $this->createClone($this->merchant->withDivision($division));
    }
    public function getToken(): string
    {
        return $this->merchant->getToken();
    }
    public function withToken(string $token): MerchantInterface
    {
        return $this->createClone($this->merchant->withToken($token));
    }
    public function getBaseUrl(): UriInterface
    {
        return $this->merchant->getBaseUrl();
    }
    public function withBaseUrl($baseUrl): MerchantInterface
    {
        return $this->createClone($this->merchant->withBaseUrl($baseUrl));
    }
    public function getTransactionUrlTemplate(): string
    {
        return $this->merchant->getTransactionUrlTemplate();
    }
    public function withTransactionUrlTemplate(string $transactionUrlTemplate): MerchantInterface
    {
        return $this->createClone($this->merchant->withTransactionUrlTemplate($transactionUrlTemplate));
    }
    /**
     * Assigns the new instance to a clone of our decorator
     *
     * @param MerchantInterface $merchant
     *
     * @return MerchantInterface
     */
    private function createClone(MerchantInterface $merchant): MerchantInterface
    {
        $clone = clone $this;
        $clone->merchant = $merchant;
        return $clone;
    }
}
