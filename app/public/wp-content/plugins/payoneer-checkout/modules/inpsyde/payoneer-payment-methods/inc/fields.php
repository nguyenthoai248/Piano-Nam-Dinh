<?php

declare (strict_types=1);
namespace Syde\Vendor;

return [
    //Payoneer-checkout gateway
    'title-payoneer-checkout' => [
        /* Title field of embedded mode */
        'title' => \__('Title', 'payoneer-checkout'),
        'type' => 'text',
        'description' => \__('The title that customers see at checkout', 'payoneer-checkout'),
        'default' => \__('Credit / Debit Card', 'payoneer-checkout'),
        'desc_tip' => \true,
        'class' => 'section-payoneer-checkout',
    ],
    //Payoneer-afterpay gateway
    'title-payoneer-afterpay' => [
        /* Title field of embedded mode */
        'title' => \__('Title', 'payoneer-checkout'),
        'type' => 'text',
        'description' => \__('The title that customers see at checkout', 'payoneer-checkout'),
        'default' => \__('Afterpay', 'payoneer-checkout'),
        'desc_tip' => \true,
        'class' => 'section-payoneer-afterpay',
    ],
    //Payoneer-hosted payment gateway
    'title-payoneer-hosted' => [
        'title' => \__('Title', 'payoneer-checkout'),
        'type' => 'text',
        /* Title field of hosted mode */
        'description' => \__('The title that customers see at checkout', 'payoneer-checkout'),
        'default' => \__('Credit / Debit Card', 'payoneer-checkout'),
        'desc_tip' => \true,
        'class' => 'section-payoneer-hosted',
    ],
    'description-payoneer-hosted' => [
        'title' => \__('Description', 'payoneer-checkout'),
        'type' => 'text',
        /* Description field of hosted mode */
        'description' => \__('The description that customers see at checkout', 'payoneer-checkout'),
        'default' => '',
        'desc_tip' => \true,
        'class' => 'section-payoneer-hosted',
    ],
    //Klarna payment gateway
    'title-payoneer-klarna' => [
        'title' => \__('Title', 'payoneer-checkout'),
        'type' => 'text',
        /* Title field of Klarna mode */
        'description' => \__('The title that customers see at checkout', 'payoneer-checkout'),
        'default' => \__('Klarna', 'payoneer-checkout'),
        'desc_tip' => \true,
        'class' => 'section-payoneer-klarna',
    ],
    'description-payoneer-klarna' => [
        'title' => \__('Description', 'payoneer-checkout'),
        'type' => 'text',
        /* Description field of Klarna mode */
        'description' => \__('The description that customers see at checkout', 'payoneer-checkout'),
        'default' => '',
        'desc_tip' => \true,
        'class' => 'section-payoneer-klarna',
    ],
    //Affirm payment gateway
    'title-payoneer-affirm' => [
        'title' => \__('Title', 'payoneer-checkout'),
        'type' => 'text',
        /* Title field of Affirm mode */
        'description' => \__('The title that customers see at checkout', 'payoneer-checkout'),
        'default' => \__('Affirm', 'payoneer-checkout'),
        'desc_tip' => \true,
        'class' => 'section-payoneer-affirm',
    ],
    'description-payoneer-affirm' => [
        'title' => \__('Description', 'payoneer-checkout'),
        'type' => 'text',
        /* Description field of Affirm mode */
        'description' => \__('The description that customers see at checkout', 'payoneer-checkout'),
        'default' => '',
        'desc_tip' => \true,
        'class' => 'section-payoneer-affirm',
    ],
];
