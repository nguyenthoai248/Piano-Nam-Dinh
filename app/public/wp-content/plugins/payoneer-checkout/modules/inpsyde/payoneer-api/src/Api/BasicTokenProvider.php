<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Api;

use Syde\Vendor\Inpsyde\PayoneerSdk\Client\TokenAwareInterface;
/**
 * Produces a basic auth token from username and password.
 */
class BasicTokenProvider implements TokenAwareInterface
{
    /** @var string */
    protected $username;
    /** @var string */
    protected $password;
    /**
     * @param string $username The username.
     * @param string $password The password.
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
    /**
     * @inheritDoc
     */
    public function getToken(): string
    {
        $username = $this->username;
        $password = $this->password;
        $token = sprintf('Basic %1$s', base64_encode($username . ':' . $password));
        return $token;
    }
}
