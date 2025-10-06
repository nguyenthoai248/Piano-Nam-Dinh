<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PageDetector\UrlPageDetectorFactory;
return static function (): array {
    return ['http.page_detector.factory' => new Constructor(UrlPageDetectorFactory::class, [])];
};
