<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

use RuntimeException;
trait JsonCodecTrait
{
    /**
     * Encodes a JSON string.
     *
     * @param mixed $value The value to encode.
     *
     * @return string The encoded value.
     *
     * @throws RuntimeException If problem encoding.
     */
    protected function jsonEncode($value): string
    {
        $result = json_encode($value);
        if ($result && json_last_error() === \JSON_ERROR_NONE) {
            return $result;
        }
        $message = json_last_error_msg();
        throw new RuntimeException($message);
    }
    /**
     * Decodes a JSON string.
     *
     * @param string $json The JSON to decode.
     *
     * @return mixed The decoded value. Objects are represented as arrays.
     *
     * @throws RuntimeException If problem decoding.
     */
    protected function jsonDecode(string $json)
    {
        $result = json_decode($json, \true);
        if (json_last_error() === \JSON_ERROR_NONE) {
            return $result;
        }
        $message = json_last_error_msg();
        throw new RuntimeException($message);
    }
}
