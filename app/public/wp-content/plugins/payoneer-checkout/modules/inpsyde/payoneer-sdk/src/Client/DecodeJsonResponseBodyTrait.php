<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

use Syde\Vendor\Psr\Http\Message\ResponseInterface;
use RuntimeException;
use UnexpectedValueException;
trait DecodeJsonResponseBodyTrait
{
    /**
     * Return parsed response body as array.
     *
     * @param ResponseInterface $response
     *
     * @return array Decoded body content.
     *
     * @throws RuntimeException If failed to parse response body.
     *
     */
    protected function decodeJsonResponseBody(ResponseInterface $response): array
    {
        try {
            $responseBody = $response->getBody();
            $responseBody->rewind();
            $bodyContent = $responseBody->getContents();
        } catch (RuntimeException $exception) {
            throw new UnexpectedValueException('Failed to read API response body.');
        }
        $decodedBody = json_decode($bodyContent, \true);
        if (!is_array($decodedBody)) {
            throw new UnexpectedValueException(sprintf('Failed to parse JSON string from API response. The string is: %1$s', $bodyContent));
        }
        return $decodedBody;
    }
}
