<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

class PassThroughListSessionProvider implements ListSessionProvider
{
    /**
     * @var ListInterface
     */
    protected $list;

    public function __construct(ListInterface $list)
    {
        $this->list = $list;
    }

    public function provide(): ListInterface
    {
        return $this->list;
    }
}
