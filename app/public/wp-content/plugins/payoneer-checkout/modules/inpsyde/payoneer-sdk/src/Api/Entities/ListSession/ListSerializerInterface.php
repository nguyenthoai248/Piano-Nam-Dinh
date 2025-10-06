<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession;

/**
 * Service able to convert ListInterface to array
 */
interface ListSerializerInterface
{
    /**
     * @param ListInterface $listSession To be serialized.
     *
     * @return array {
     *     links: array{self: string, lang?: string, customer?: string},
     *     identification: array{longId: string, shortId: string, transactionId: string, pspId?: string},
     *     customer?: array{number: string, email?: string, deliveryEmail?: string}
     *     payment: array{reference: string, amount: float, currency: string},
     *     status: array{code: string, reason: string},
     *     style: array{language: string},
     *     redirect?: array{url: string, method: string, type: string},
     *     products: array{
     *      type: ProductType::*,
     *      code: string,
     *      name: string,
     *      amount: float,
     *      currency: string,
     *      quantity: int,
     *      netAmount: float,
     *      taxAmount: float
     *      productDescriptionUrl?: string,
     *      productImageUrl?: string,
     *      description?: string,
     *      taxCode?: string,
     *     }[]
     *     division?: string
     * } Serialized LIST session
     */
    public function serializeListSession(ListInterface $listSession): array;
}
