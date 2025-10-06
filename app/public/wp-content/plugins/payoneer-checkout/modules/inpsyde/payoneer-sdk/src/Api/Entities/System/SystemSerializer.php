<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System;

class SystemSerializer implements SystemSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeSystem(SystemInterface $system): array
    {
        return ['type' => $system->getType(), 'code' => $system->getCode(), 'version' => $system->getVersion()];
    }
}
