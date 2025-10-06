<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

use RuntimeException;
use UnexpectedValueException;
/**
 * Cam serialize a Merchant into a DTO, and vice-versa.
 */
class MerchantSerializer implements MerchantSerializerInterface, MerchantDeserializerInterface
{
    /** @var MerchantInterface */
    protected $merchant;
    public function __construct(MerchantInterface $merchant)
    {
        $this->merchant = $merchant;
    }
    /**
     * @inheritDoc
     */
    public function serializeMerchant(MerchantInterface $merchant) : array
    {
        $dto = ['label' => $merchant->getLabel(), 'environment' => $merchant->getEnvironment(), 'code' => $merchant->getCode(), 'division' => $merchant->getDivision(), 'token' => $merchant->getToken(), 'base_url' => (string) $merchant->getBaseUrl(), 'transaction_url_template' => $merchant->getTransactionUrlTemplate()];
        try {
            $dto['id'] = $merchant->getId();
            // phpcs:ignore Inpsyde.CodeQuality.ElementNameMinimalLength.TooShort
        } catch (UnexpectedValueException $e) {
            $dto['id'] = null;
        }
        return $dto;
    }
    /**
     * @inheritDoc
     */
    public function deserializeMerchant(array $dto) : MerchantInterface
    {
        $merchant = $this->createMerchant($dto['id'] ?? null);
        isset($dto['environment']) && ($merchant = $merchant->withEnvironment($dto['environment']));
        isset($dto['code']) && ($merchant = $merchant->withCode($dto['code']));
        isset($dto['division']) && ($merchant = $merchant->withDivision($dto['division']));
        isset($dto['token']) && ($merchant = $merchant->withToken($dto['token']));
        isset($dto['base_url']) && ($merchant = $merchant->withBaseUrl($dto['base_url']));
        isset($dto['transaction_url_template']) && ($merchant = $merchant->withTransactionUrlTemplate($dto['transaction_url_template']));
        isset($dto['label']) && ($merchant = $merchant->withLabel($dto['label']));
        return $merchant;
    }
    /**
     * Creates a new Merchant.
     *
     * @param ?positive-int $id The merchant ID.
     *
     * @return MerchantInterface The new Merchant.
     *
     * @throws RuntimeException If problem creating.
     */
    protected function createMerchant(?int $id) : MerchantInterface
    {
        $template = $this->merchant;
        $merchant = $template->withId($id);
        return $merchant;
    }
}
