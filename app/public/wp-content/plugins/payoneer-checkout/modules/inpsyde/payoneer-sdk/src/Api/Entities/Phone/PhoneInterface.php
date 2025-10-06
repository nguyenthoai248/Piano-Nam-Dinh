<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone;

/**
 * Represents phone number. For now, here is unstructured phone only. In the future,
 * it's possible to have country code, area code and subscriber number separately.
 */
interface PhoneInterface
{
    /**
     * @return string Return unstructured phone number.
     */
    public function getUnstructuredNumber(): string;
}
