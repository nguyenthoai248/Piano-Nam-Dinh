<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

use Exception;
use Throwable;
class ApiClientException extends Exception implements ApiClientExceptionInterface
{
    /**
     * @var ApiClientInterface
     */
    protected $client;
    /**
     * @param ApiClientInterface $client The API client thrown exception.
     */
    public function __construct(ApiClientInterface $client, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->client = $client;
        parent::__construct($message, $code, $previous);
    }
    /**
     * @inheritDoc
     */
    public function getClient(): ApiClientInterface
    {
        return $this->client;
    }
}
