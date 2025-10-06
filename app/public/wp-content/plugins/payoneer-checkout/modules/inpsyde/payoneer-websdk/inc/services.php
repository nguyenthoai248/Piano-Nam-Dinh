<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\WebSdk\Security\SdkIntegrityService;
return static function (): array {
    return ['websdk.assets.umd.url.template' => new Factory(['websdk.assets.js.suffix'], static function (string $jsSuffix) {
        return "https://resources.<env>.oscato.com/web/libraries/checkout-web/umd/checkout-web<version>" . $jsSuffix;
    }), 'websdk.assets.js.suffix' => new Factory(['wp.is_script_debug'], static function (bool $isScriptDebug): string {
        return $isScriptDebug ? '.js' : '.min.js';
    }), 'websdk.security.environment_version_map' => new Value(['sandbox' => '1.21.0', 'live' => '1.21.0']), 'websdk.security.integrity_hashes' => new Value(['1.14.0' => ['.js' => 'sha384-4U0ZFrafaj0LxeiUF1YgP0uhm4wi8q6S2hmVpduGutj8PFArn8rbm3EYhmwwPgpc', '.min.js' => 'sha384-s31eL4e9J9mZElaihKGnBZlwxtLk/Rt07nAgnJA6MCMMEt8VrHbaupZeVVughfP+'], '1.17.0' => ['.js' => 'sha384-I3pqtB6Fb5chZeCjzaufzTGTHXWzAwB1bHRtyQHTMqabQ4I+RCTk+aU+jwwDc41x', '.min.js' => 'sha384-+hKaXLbR4WnSyv3xTQ6WCICdWlBi9IgCRcadmSoeXk6YPmdfuCZdE//hquwM3fgt'], '1.19.0' => ['.js' => 'sha384-/dWJOglT1fwY0uQZ6GVuCkyBYOtXKzNm7Ocj+pvIx5PZjguhrcACBdXnvKJusp4g', '.min.js' => 'sha384-KUz+Gi/0b1jfEmckaU2sEXBnyRGC8q6P1Vs2P4vfNXgNnarc3WHb0syLJS7Vttid'], '1.20.0' => ['.js' => 'sha384-cfGdOxBUd49hE70WURjVUQYtie2Qi/XKarhWUsTZESgTujuRAl3orjAcRfIxcKdX', '.min.js' => 'sha384-NNHzJQswCsJs5qwSZ6KPLQWr6ojJqEIF6sCYHjH3UhQPkwipB/0W12qfnlxsI8WA'], '1.21.0' => ['.js' => 'sha384-I4aWodS3qw0OnNC6Xndev5KOW3Uqu/4SiuqCeVNcHvKL/4em3gP3X+az5tqFcs4M', '.min.js' => 'sha384-BoiN9h2YZWMoetOi4fYRMeZx/HuSjHdCsHsZWh8QGvaZRUDB67MB81yuVVaY3cKp']]), 'websdk.security.integrity' => new Constructor(SdkIntegrityService::class, ['websdk.assets.js.suffix', 'websdk.security.environment_version_map', 'websdk.security.integrity_hashes'])];
};
