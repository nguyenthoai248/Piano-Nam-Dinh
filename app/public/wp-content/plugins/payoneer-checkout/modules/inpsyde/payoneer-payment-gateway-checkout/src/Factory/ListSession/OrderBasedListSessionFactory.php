<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\FactoryException;
use Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Order;

class OrderBasedListSessionFactory implements OrderBasedListSessionFactoryInterface
{
    /**
     * @var OrderBasedListCommandFactory
     */
    protected $orderBasedListCommandFactory;

    /**
     * @param OrderBasedListCommandFactory $orderBasedListCommandFactory
     */
    public function __construct(
        OrderBasedListCommandFactory $orderBasedListCommandFactory
    ) {

        $this->orderBasedListCommandFactory = $orderBasedListCommandFactory;
    }

    /**
     * @inheritDoc
     */
    public function createList(WC_Order $order, string $listSecurityToken): ListInterface
    {
        $createListCommand = $this->orderBasedListCommandFactory
            ->createListCommand($order)
            ->withOperationType('CHARGE');

        do_action('payoneer-checkout.before_create_list');

        try {
            $list = $createListCommand->execute();

            do_action(
                'payoneer-checkout.list_session_created',
                [
                    'longId' => $list->getIdentification()->getLongId(),
                    'list' => $list,
                ]
            );

            return $list;
        } catch (CommandExceptionInterface $exception) {
            do_action(
                'payoneer-checkout.create_list_session_failed',
                ['exception' => $exception]
            );
            throw new FactoryException('Could not create LIST session', 0, $exception);
        }
    }
}
