<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class RedirectDeserializer implements RedirectDeserializerInterface
{
    /**
     * @var RedirectFactoryInterface
     */
    protected $redirectFactory;
    /**
     * @param RedirectFactoryInterface $redirectFactory A factory to create redirect instance.
     */
    public function __construct(RedirectFactoryInterface $redirectFactory)
    {
        $this->redirectFactory = $redirectFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeRedirect(array $redirectData): RedirectInterface
    {
        if (!isset($redirectData['url'])) {
            throw new ApiException('Data contains no expected url element.');
        }
        $url = $redirectData['url'];
        if (!isset($redirectData['method'])) {
            throw new ApiException('Data contains no expected method element.');
        }
        $method = $redirectData['method'];
        $type = $redirectData['type'] ?? 'DEFAULT';
        $parameters = $redirectData['parameters'] ?? [];
        return $this->redirectFactory->createRedirect($url, $method, $type, $parameters);
    }
}
