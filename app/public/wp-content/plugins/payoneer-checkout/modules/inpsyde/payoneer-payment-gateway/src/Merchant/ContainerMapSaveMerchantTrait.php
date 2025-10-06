<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

use Syde\Vendor\Dhii\Collection\MutableContainerInterface;
use Syde\Vendor\Psr\Container\ContainerExceptionInterface;
use RangeException;
use RuntimeException;
/**
 * Functionality for saving a Merchant to a mutable map in the form of a map of Merchant DTOs.
 *
 * @psalm-type MerchantData = array{
 *  id?: ?positive-int,
 *  label?: string,
 *  code?: string,
 *  token?: string,
 *  base_url?: string,
 *  transaction_url_template?: string
 * }
 */
trait ContainerMapSaveMerchantTrait
{
    /** @var MutableContainerInterface */
    protected $storage;
    /** @var string */
    protected $storageKey;
    /** @var MerchantSerializerInterface */
    protected $serializer;
    /**
     * @inheritDoc
     */
    public function saveMerchant(MerchantInterface $merchant) : MerchantInterface
    {
        $merchants = $this->retrieve();
        $merchantId = $merchant->getId() ?? count($merchants);
        $merchants[$merchantId] = $this->serializeMerchant($merchant);
        $this->persist($merchants);
        $merchant = $this->load($merchantId);
        return $merchant;
    }
    /**
     * Saves a list of merchants to storage.
     *
     *
     * @psalm-suppress InvalidCatch These are actually exception interfaces.
     * @param array<int, array> $merchants A list of Merchant DTOs.
     * @psalm-param array<int, MerchantData> $merchants
     *
     * @throws RuntimeException If problem saving.
     */
    protected function persist(array $merchants) : void
    {
        try {
            $this->storage->set($this->storageKey, $merchants);
            // phpcs:ignore Inpsyde.CodeQuality.ElementNameMinimalLength.TooShort
        } catch (ContainerExceptionInterface $e) {
            throw new RuntimeException('Could not save merchants');
        }
    }
    /**
     * Transforms a Merchant into its TDO.
     *
     * @param MerchantInterface $merchant
     *
     * @return MerchantData The Merchant data.
     *
     * @throws RuntimeException If problem transforming.
     */
    protected function serializeMerchant(MerchantInterface $merchant) : array
    {
        return $this->serializer->serializeMerchant($merchant);
    }
    /**
     * Retrieves a list of merchants from storage.
     *
     * @return array<int, MerchantData> A list of Merchant DTOs.
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected abstract function retrieve() : array;
    /**
     * Transforms a DTO into a Merchant.
     *
     * @param MerchantData $merchant The Merchant data.
     *
     * @return MerchantInterface The Merchant.
     *
     * @throws RuntimeException If problem transforming.
     */
    protected abstract function deserializeMerchant(array $merchant) : MerchantInterface;
    /**
     * Retrieves a single Merchant by ID from storage.
     *
     * @param int $merchantId The ID of the Merchant to load.
     *
     * @return MerchantInterface The merchant.
     *
     * @throws RangeException If no Merchant found for the specified ID.
     * @throws RuntimeException If problem retrieving.
     */
    protected abstract function load(int $merchantId) : MerchantInterface;
}
