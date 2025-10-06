<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect;

class RedirectSerializer implements RedirectSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeRedirect(RedirectInterface $redirect): array
    {
        return ['url' => $redirect->getUrl(), 'method' => $redirect->getMethod(), 'type' => $redirect->getType(), 'parameters' => $redirect->getParameters()];
    }
}
