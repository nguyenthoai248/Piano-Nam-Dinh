<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class Style implements StyleInterface
{
    /**
     * @var string|null
     */
    protected $language;
    /**
     * @var ?string
     */
    protected $hostedVersion;
    /**
     * @param string|null $language Language code for payment fields.
     */
    public function __construct(string $language = null)
    {
        $this->language = $language;
    }
    /**
     * @inheritDoc
     */
    public function getLanguage(): string
    {
        if (!$this->language) {
            throw new ApiException('lang is not set in Style object');
        }
        return $this->language;
    }
    public function getHostedVersion(): string
    {
        if (!$this->hostedVersion) {
            throw new ApiException('hostedVersion not set in Style object');
        }
        return $this->hostedVersion;
    }
    public function withHostedVersion(string $hostedVersion): StyleInterface
    {
        $new = clone $this;
        $new->hostedVersion = $hostedVersion;
        return $new;
    }
}
