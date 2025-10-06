<?php

/**
 * The email template intentionally uses INLINE STYLES and deprecated attributes,
 * as this is the most reliable way to format the message inside an email client.
 */
declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Value;
/**
 * Placeholders:
 * {SITE_URL} .. WordPress home URL, helps to identify the shop.
 * {MERCHANT_CODE} .. Identifies the merchant.
 * {STORE_CODE} .. Identifies the merchant's division/store.
 * {ORDER_IDS} .. Which orders are included in the attached log.
 * {LOG_INFO} .. Date and source of the log file.
 */
return new Value(<<<END_OF_TEMPLATE
<!DOCTYPE html>
<html>
<head>
    <style type="text/css">
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans,
            Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        font-size: 13px;
        margin: 0;
        padding: 0;
        line-height: 1.5;
        background: #f0f0f1;
        color: #2c3338;
    }

    div#main {
        background: #ffffff;
        max-width: 800px;
        margin: 20px auto;
        padding: 15px 20px;
        box-sizing: border-box;
    }
    </style>
</head>
<body>
    <div id="main">
        <p>
            System report from <strong>{SITE_NAME}</strong>
            (<a href="{SITE_URL}" target="_blank">{SITE_URL}</a>)
        </p>
        <p>
            <em>Please check the attached files for detailed information.</em>
        </p>

        <table border="0" cellpadding="4" cellspacing="0" style="
            border:1px solid #c5c5c5;width:100%">
            <tr style="background-color:#f0f0f0">
                <th style="width:150px;text-align:left;vertical-align:top">Merchant code:</th>
                <td>{MERCHANT_CODE}</td>
            </tr>
            <tr style="background-color:#ffffff">
                <th style="width:150px;text-align:left;vertical-align:top">Store code:</th>
                <td>{STORE_CODE}</td>
            </tr>
            <tr style="background-color:#f0f0f0">
                <th style="width:150px;text-align:left;vertical-align:top">Order(s):</th>
                <td>{ORDER_IDS}</td>
            </tr>
            <tr style="background-color:#ffffff">
                <th style="width:150px;text-align:left;vertical-align:top">Log details:</th>
                <td>{LOG_INFO}</td>
            </tr>
        </table>
    </div>
</body>
</html>
END_OF_TEMPLATE
);
