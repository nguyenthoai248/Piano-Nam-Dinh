<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class Registration implements RegistrationInterface
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string|null
     */
    protected $password;
    /**
     * @param string $id
     * @param string|null $password
     */
    public function __construct(string $id, string $password = null)
    {
        $this->id = $id;
        $this->password = $password;
    }
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }
    /**
     * @inheritDoc
     */
    public function getPassword(): string
    {
        if ($this->password !== null) {
            return $this->password;
        }
        throw new ApiException('password field is not set');
    }
}
