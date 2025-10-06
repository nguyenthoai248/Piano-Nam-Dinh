<?php

/**
 * The template is used to build HTML documents that are attached to the
 * status report email.
 */
declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Value;
/**
 * Placeholders:
 *
 * {BODY_CLASSES} .. Additional CSS classes, e.g. "is-error"
 * {DOCUMENT_TITLE} .. the HTML title
 * {DOCUMENT_HEADER} .. H1 title, usually shorter
 * {DOCUMENT_BODY} .. HTML of the actual body
 * {DOCUMENT_FOOTER} .. Footer, something like a timestamp
 */
return new Value(<<<END_OF_TEMPLATE
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{DOCUMENT_TITLE}</title>
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

        h1, h2 {
            color: #23282d;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }

        header.header, footer.footer {
            font-size: 13px;
            font-weight: 400;
            line-height: 12px;
            padding: 10px 20px;
            box-sizing: border-box;
        }

        header.header {
            background: #1d2327;
            color: #e0e0e1;
        }

        header.header strong {
            color: #ffffff;
            font-weight: 500;
        }

        footer.footer {
            text-align: right;
            color: #60676e;
        }

        footer.footer strong {
            color: #30373e;
            font-weight: 500;
        }

        main.main {
            max-width: 800px;
            margin: 20px auto;
            padding: 0;
        }

        p {
            margin: 0.5em 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
            font-size: 1.1em;
            border: 1.5px solid #c3c4c7;
            background: #ffffff;
        }

        table h1, table h2 {
            margin: 0;
        }

        th, td {
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #dddddd;
            vertical-align: top;
        }

        th:first-child, td:first-child {
            border-left-color: #c3c4c7
        }

        th:last-child, td:last-child {
            border-right-color: #c3c4c7
        }

        tr:first-child > th, tr:first-child > td {
            border-top-color: #c3c4c7
        }

        tr:last-child > th, tr:last-child > td {
            border-bottom-color: #c3c4c7
        }

        th {
            font-weight: bold;
            width: 30%;
        }

        tr:nth-child(even) {
            background-color: #f6f6f6;
        }

        .section {
            margin-bottom: 30px;
        }

        /* Status report */
        table.wc_status_table td:first-child {
            width: 33%;
        }

        table.wc_status_table mark {
            background: transparent;
        }

        table.wc_status_table mark:before {
            display: none;
            margin: 0 10px 0 0;
            background: #888888;
            color: #ffffff;
            padding: 4px;
            font-weight: bold;
            border-radius: 12px;
            font-size: 12px;
            width: 12px;
            height: 12px;
            line-height: 12px;
            text-align: center;
        }

        table.wc_status_table .yes {
            color: #7ad03a;
        }

        table.wc_status_table .error {
            color: #aa0000;
        }

        table.wc_status_table .yes:before {
            content: \\'✔︎\\';
            display: inline-block;
            background: #579825;
        }

        table.wc_status_table .error:before {
            content: \\' ! \\';
            display: inline-block;
            background: #aa0000;
        }

        /* Generic error reports */
        .is-error h1 {
            color: #dc3232;
        }

        .error-code {
            font-weight: bold;
            margin-top: 20px;
        }

        .error-message {
            color: #dc3232;
        }
    </style>
</head>
<body>
    <header class="header"><span class="site-name">{DOCUMENT_TITLE}</span></header>
    <main class="main {BODY_CLASSES}">
        <h1>{DOCUMENT_HEADER}</h1>
        <div>{DOCUMENT_BODY}</div>
    </main>
    <footer class="footer">{DOCUMENT_FOOTER}</footer>
</body>
</html>
END_OF_TEMPLATE
);
