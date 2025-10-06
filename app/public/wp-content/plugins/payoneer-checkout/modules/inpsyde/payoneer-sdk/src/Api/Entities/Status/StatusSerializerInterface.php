<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Status;

/**
 * Service able to serialize StatusInterface instance.
 */
interface StatusSerializerInterface
{
    /**
     * @param StatusInterface $status
     *
     * @return array{code: string, reason: string}
     */
    public function serializeStatus(StatusInterface $status): array;
}
