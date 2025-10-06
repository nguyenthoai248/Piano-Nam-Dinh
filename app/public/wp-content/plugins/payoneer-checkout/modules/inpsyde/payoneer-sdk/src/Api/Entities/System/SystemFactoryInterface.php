<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System;

interface SystemFactoryInterface
{
    /**
     * @param string $type
     * @param string $code
     * @param string $version
     *
     * @return SystemInterface
     */
    public function createSystem(string $type, string $code, string $version): SystemInterface;
}
