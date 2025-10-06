<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

use Syde\Vendor\Psr\Container\ContainerExceptionInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Psr\Container\NotFoundExceptionInterface;
use RangeException;
use RuntimeException;
/**
 * Can query merchants from a map stored in a container.
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
trait ContainerMapMerchantQueryTrait
{
    /** @var ContainerInterface */
    protected $storage;
    /** @var string */
    protected $storageKey;
    /** @var MerchantDeserializerInterface */
    protected $deserializer;
    /** @var ?int */
    protected $merchantId;
    /**
     * Creates an instance with the specified Merchant ID.
     *
     * @param int $id The Merchant ID.
     *
     * @return static A new instance with the specified Merchant ID.
     */
    public function withId(int $id) : self
    {
        $new = clone $this;
        $new->merchantId = $id;
        return $new;
    }
    /**
     * @inheritDoc
     */
    public function execute() : iterable
    {
        $dtos = $this->retrieve();
        /** @psalm-var array<MerchantData> */
        $dtos = $this->filter($dtos);
        $merchants = [];
        foreach ($dtos as $dto) {
            $merchants[] = $this->deserializeMerchant($dto);
        }
        return $merchants;
    }
    /**
     * Filters a list of items according to the specified criteria.
     *
     * @param array $items The list of items to filter.
     *
     * @return array A new list with only the items that match the criteria.
     *
     * @throws RuntimeException If problem filtering.
     */
    protected function filter(array $items) : array
    {
        $predicate = function (array $item) : bool {
            $merchantId = $this->merchantId;
            if ($merchantId === null) {
                return \true;
            }
            return isset($item['id']) && $item['id'] === $merchantId;
        };
        return array_filter($items, $predicate);
    }
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
    protected function load(int $merchantId) : MerchantInterface
    {
        $merchants = $this->retrieve();
        if (!array_key_exists($merchantId, $merchants)) {
            throw new RangeException(sprintf('Merchant not found for ID "%1$d"', $merchantId));
        }
        $dto = $merchants[$merchantId];
        $merchant = $this->deserializeMerchant($dto);
        return $merchant;
    }
    /**
     * Transforms a DTO into a Merchant.
     *
     * @param array $merchant The Merchant data.
     * @psalm-param MerchantData $merchant
     *
     * @return MerchantInterface The Merchant.
     *
     * @throws RuntimeException If problem transforming.
     */
    protected function deserializeMerchant(array $merchant) : MerchantInterface
    {
        return $this->deserializer->deserializeMerchant($merchant);
    }
    /**
     * Retrieves a list of merchants from storage.
     *
     * @psalm-suppress MixedInferredReturnType Merchant structure integrity verified
     *                 by serialization.
     * @psalm-suppress InvalidCatch These are actually exception interfaces.
     * @return array<int, MerchantData> A list of Merchant DTOs.
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected function retrieve() : array
    {
        try {
            return $this->storage->get($this->storageKey);
            // phpcs:ignore Inpsyde.CodeQuality.ElementNameMinimalLength.TooShort
        } catch (NotFoundExceptionInterface $e) {
            return [];
            // phpcs:ignore Inpsyde.CodeQuality.ElementNameMinimalLength.TooShort
        } catch (ContainerExceptionInterface $e) {
            throw new RuntimeException('Could not retrieve merchants');
        }
    }
}
