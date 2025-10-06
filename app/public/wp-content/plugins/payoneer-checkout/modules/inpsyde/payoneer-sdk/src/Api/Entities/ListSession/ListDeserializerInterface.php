<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
/**
 * Service able to convert array to List instance.
 */
interface ListDeserializerInterface
{
    /**
     * @param array{links: array,
     *              identification: array {
     *                  longId: string,
     *                  shortId: string,
     *                  transactionId: string,
     *                  pspId?: string
     *              },
     *              customer?: array,
     *              payment: array,
     *              status: array {
     *                  code: string,
     *                  reason: string
     *              },
     *              redirect?: array {
     *                  url: string,
     *                  method: string,
     *                  type: string
     *              },
     *              division?: string,
     *              products?: array{
     *                  type: ProductType::*,
     *                  code: string,
     *                  name: string,
     *                  amount: float,
     *                  currency: string,
     *                  quantity: int,
     *                  netAmount: float,
     *                  taxAmount: float,
     *                  productDescriptionUrl?: string,
     *                  productImageUrl?: string,
     *                  description?: string,
     *                  taxCode?: string,
     *              }[],
     *              division?: string
     * } $listData
     *
     * @return ListInterface Created instance.
     *
     * @throws ApiExceptionInterface If something went wrong.
     */
    public function deserializeList(array $listData): ListInterface;
}
