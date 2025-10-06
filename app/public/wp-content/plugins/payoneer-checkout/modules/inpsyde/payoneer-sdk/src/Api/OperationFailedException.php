<?php

namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api;

class OperationFailedException extends ApiException implements OperationExceptionInterface
{
    /**
     * @var array
     */
    protected $responseBody;
    /**
     * @var array
     */
    protected $requestBody;
    public function __construct(string $messageBase, array $responseBody, array $requestBody, int $code = 0, ?\Throwable $previous = null)
    {
        $this->message = $messageBase;
        $this->responseBody = $responseBody;
        $this->requestBody = $requestBody;
        parent::__construct($messageBase, $code, $previous);
    }
    public function getRawRequest(): array
    {
        return $this->requestBody;
    }
    public function getRawResponse(): array
    {
        return $this->responseBody;
    }
}
