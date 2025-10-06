<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\InteractionCodeFailureInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\DecodeJsonResponseBodyTrait;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ResponseValidatorInterface;
use Syde\Vendor\Psr\Http\Message\ResponseInterface;
use RangeException;
use RuntimeException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\Command\ValidationFailure;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\Command\ValidatorFailureInterface;
/**
 * phpcs:disable Inpsyde.CodeQuality.ElementNameMinimalLength
 */
abstract class AbstractCommand implements CommandInterface
{
    use DecodeJsonResponseBodyTrait;
    /** @var ApiClientInterface */
    protected $apiClient;
    /** @var ListDeserializerInterface */
    protected $listDeserializer;
    /** @var string A template of path part of request URL. */
    protected $pathTemplate;
    /** @var ResponseValidatorInterface */
    protected $responseValidator;
    /** @var ?string Unique session id set from the Payoneer side. */
    protected $longId;
    /** @var ?string TransactionId defined by merchant. */
    protected $transactionId;
    /** @var array<string, InteractionErrorInterface> */
    protected $errors;
    /**
     * @param ApiClientInterface $apiClient
     * @param ListDeserializerInterface $listDeserializer
     * @param string $pathTemplate
     * @param ResponseValidatorInterface $responseValidator
     * @param array<string, InteractionErrorInterface> $errors
     */
    public function __construct(ApiClientInterface $apiClient, ListDeserializerInterface $listDeserializer, string $pathTemplate, ResponseValidatorInterface $responseValidator, array $errors)
    {
        $this->apiClient = $apiClient;
        $this->listDeserializer = $listDeserializer;
        $this->pathTemplate = $pathTemplate;
        $this->responseValidator = $responseValidator;
        $this->errors = $errors;
    }
    /**
     * @inheritDoc
     */
    public function withApiClient(ApiClientInterface $apiClient): CommandInterface
    {
        $newThis = clone $this;
        $newThis->apiClient = $apiClient;
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
    /**
     * Does something with a response.
     *
     * @param ResponseInterface $response The response.
     *
     * @throws CommandExceptionInterface If something goes wrong.
     */
    protected function onResponse(ResponseInterface $response): void
    {
        try {
            $this->validateResponse($response);
        } catch (InteractionCodeFailureInterface $e) {
            $interactionCode = $e->getInteractionCode();
            if ($error = $this->errors[$interactionCode] ?? null) {
                $exception = $error->withInteractionCode($interactionCode)->withCommand($this)->withInnerException($e)->createException();
                throw $exception;
            }
        } catch (RuntimeException $e) {
            throw new CommandException($this, 'Problem validating response', 0, $e);
        }
    }
    /**
     * Validates a response.
     *
     * @param ResponseInterface $response The response to validate.
     *
     * @psalm-suppress MissingThrowsDocblock ValidatorFailureInterface|ValidationFailureInterface
     * @throws RangeException If invalid.
     * @throws RuntimeException If problem validating.
     */
    protected function validateResponse(ResponseInterface $response): void
    {
        $this->responseValidator->validateResponse($response);
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
    abstract protected function jsonDecode(string $json);
}
