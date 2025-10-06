<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System;

use InvalidArgumentException;
class SystemDeserializer implements SystemDeserializerInterface
{
    /**
     * @var SystemFactoryInterface
     */
    protected $systemFactory;
    /**
     * @param SystemFactoryInterface $systemFactory
     */
    public function __construct(SystemFactoryInterface $systemFactory)
    {
        $this->systemFactory = $systemFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeSystem(array $systemData): SystemInterface
    {
        if (!isset($systemData['type'])) {
            throw new InvalidArgumentException('System data contains no expected `type` element.');
        }
        if (!isset($systemData['code'])) {
            throw new InvalidArgumentException('System data contains no expected `code` element.');
        }
        if (!isset($systemData['version'])) {
            throw new InvalidArgumentException('System data contains no expected `version` element.');
        }
        return $this->systemFactory->createSystem($systemData['type'], $systemData['code'], $systemData['version']);
    }
}
