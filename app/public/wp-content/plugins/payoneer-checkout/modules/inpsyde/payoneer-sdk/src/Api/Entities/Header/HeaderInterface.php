<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header;

/**
 * Represents an HTTP header. This expected to be added to the Callback in Notification headers list.
 */
interface HeaderInterface
{
    /**
     * Return header name.
     *
     * @return string
     */
    public function getName(): string;
    /**
     * Return header value.
     *
     * @return string
     */
    public function getValue(): string;
}
