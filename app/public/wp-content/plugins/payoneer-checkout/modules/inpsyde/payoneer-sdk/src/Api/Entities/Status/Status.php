<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Status;

class Status implements StatusInterface
{
    /**
     * @var string A short string explaining current session status.
     */
    protected $code;
    /**
     * @var string A longer string explaining status code.
     */
    protected $reason;
    /**
     * @param string $code Status code, like 'pending', 'declined', 'charged', etc.
     *                     See the {@link https://www.optile.io/opg#285186 full list} of codes.
     * @param string $reason A short phrase explaining status code.
     */
    public function __construct(string $code, string $reason)
    {
        $this->code = $code;
        $this->reason = $reason;
    }
    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }
    /**
     * @inheritDoc
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
