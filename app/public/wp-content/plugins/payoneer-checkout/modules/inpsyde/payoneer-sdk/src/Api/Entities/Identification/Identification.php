<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class Identification implements IdentificationInterface
{
    /**
     * @var string Unique session identifier, defined by Payoneer.
     */
    protected $longId;
    /**
     * @var string Non-unique session identifier, defined by Payoneer.
     */
    protected $shortId;
    /**
     * @var string Identifier of transaction, defined by merchant.
     */
    protected $transactionId;
    /**
     * @var string|null Identifier of transaction, defined by payment service provider.
     */
    protected $pspId;
    /**
     * @param string $longId Unique payment session identifier.
     * @param string $shortId Non-unique session identifier.
     * @param string $transactionId Session id defined by merchant.
     * @param string|null $pspId Identifier assigned by a PSP.
     */
    public function __construct(string $longId, string $shortId, string $transactionId, string $pspId = null)
    {
        $this->longId = $longId;
        $this->shortId = $shortId;
        $this->transactionId = $transactionId;
        $this->pspId = $pspId;
    }
    /**
     * @inheritDoc
     */
    public function getLongId(): string
    {
        return $this->longId;
    }
    /**
     * @inheritDoc
     */
    public function getShortId(): string
    {
        return $this->shortId;
    }
    /**
     * @inheritDoc
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }
    /**
     * @inheritDoc
     */
    public function getPspId(): string
    {
        if ($this->pspId === null) {
            throw new ApiException('pspId field is not set');
        }
        return $this->pspId;
    }
}
