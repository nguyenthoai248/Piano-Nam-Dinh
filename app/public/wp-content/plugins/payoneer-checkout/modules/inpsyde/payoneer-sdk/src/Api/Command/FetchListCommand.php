<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\DecodeJsonResponseBodyTrait;
use Syde\Vendor\Inpsyde\PayoneerSdk\PayoneerSdkExceptionInterface;
use RuntimeException;
class FetchListCommand extends AbstractCommand implements ListAwareCommandInterface
{
    use DecodeJsonResponseBodyTrait;
    use PrepareRequestUrlPathTrait;
    protected function jsonDecode(string $json)
    {
        return json_decode($json, \true);
    }
    public function execute(): ListInterface
    {
        try {
            $url = $this->prepareRequestUrlPath();
            $queryParams = [];
            $response = $this->apiClient->get($url, [], $queryParams);
            $this->onResponse($response);
            $parsedBody = $this->decodeJsonResponseBody($response);
            return $this->listDeserializer->deserializeList($parsedBody);
        } catch (PayoneerSdkExceptionInterface|RuntimeException $exception) {
            throw new CommandException($this, sprintf('Failed to fetch list session %1$s: %2$s', $this->longId ?? '', $exception->getMessage()), 0, $exception);
        }
    }
    public function withLongId(string $longId): ListAwareCommandInterface
    {
        $new = clone $this;
        $new->longId = $longId;
        return $new;
    }
    public function getLongId(): ?string
    {
        return $this->longId;
    }
}
