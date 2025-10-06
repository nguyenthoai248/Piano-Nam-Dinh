<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

class TokenProvider implements TokenAwareInterface
{
    /**
     * @var callable
     */
    protected $callback;
    /**
     * @param callable(): string $callback A callable returning token string.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }
    /**
     * @inheritDoc
     */
    public function getToken(): string
    {
        static $token;
        if (!$token) {
            $token = ($this->callback)();
        }
        return $token;
    }
}
