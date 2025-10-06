<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use ReturnTypeWillChange;
abstract class AbstractContext implements ContextInterface
{
    /**
     * @var array
     */
    private $data = [];
    /**
     * @param string|int $offset
     *
     * @return bool
     */
    public function offsetExists($offset = ''): bool
    {
        return isset($this->data[$offset]);
    }
    #[ReturnTypeWillChange]
    public function offsetGet($offset = '')
    {
        /**
         * @psalm-suppress MixedArrayOffset
         * @psalm-suppress MixedReturnStatement
         */
        return $this->data[$offset];
    }
    /**
     * @param string|int $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset = '', $value = ''): void
    {
        $this->data[$offset] = $value;
    }
    /**
     * @param string|int $offset
     *
     * @return void
     */
    public function offsetUnset($offset = ''): void
    {
        unset($this->data[$offset]);
    }
}
