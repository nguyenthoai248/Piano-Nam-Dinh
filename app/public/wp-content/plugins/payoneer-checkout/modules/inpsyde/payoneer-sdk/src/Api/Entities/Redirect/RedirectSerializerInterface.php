<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect;

/**
 * A service able to convert Redirect object into array.
 */
interface RedirectSerializerInterface
{
    /**
     * @param RedirectInterface $redirect A redirect instance to serialize.
     *
     * @return array{url: string, method: string, type: string, parameters: array} Serialized redirect.
     */
    public function serializeRedirect(RedirectInterface $redirect): array;
}
