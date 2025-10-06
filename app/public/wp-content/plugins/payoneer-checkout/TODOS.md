### TODOs
| Filename | line # | TODO |
|:------|:------:|:------|
| [modules.local/analytics/inc/services.php](modules.local/analytics/inc/services.php#L306) | 306 | replace with production value |
| [modules.local/analytics/inc/services.php](modules.local/analytics/inc/services.php#L309) | 309 | replace with production value |
| [modules.local/checkout/inc/services.php](modules.local/checkout/inc/services.php#L132) | 132 | use min.js when script debug is enabled |
| [modules.local/checkout/inc/services.php](modules.local/checkout/inc/services.php#L285) | 285 | consider adding our own hidden block to checkout for our custom fields, |
| [modules.local/checkout/src/CheckoutModule.php](modules.local/checkout/src/CheckoutModule.php#L339) | 339 | supply a proper css file for this. Rework markup into something more responsive |
| [modules.local/embedded-payment/src/EmbeddedPaymentModule.php](modules.local/embedded-payment/src/EmbeddedPaymentModule.php#L221) | 221 | Remove this check when support for these versions is dropped |
| [modules.local/embedded-payment/src/EmbeddedPaymentModule.php](modules.local/embedded-payment/src/EmbeddedPaymentModule.php#L310) | 310 | Refactor the environment detection to use the current WP options. |
| [modules.local/embedded-payment/src/EmbeddedPaymentModule.php](modules.local/embedded-payment/src/EmbeddedPaymentModule.php#L314) | 314 | Determine whether we can get SRI details (URL + hash) from the LIST response, and drop this service. |
| [modules.local/embedded-payment/src/EmbeddedPaymentModule.php](modules.local/embedded-payment/src/EmbeddedPaymentModule.php#L321) | 321 | Consider returning the full SDK URL instead of only the environment. We could return the final `websdk.assets.umd.url.template` here. |
| [modules.local/embedded-payment/src/ListLongIdPaymentRequestValidator.php](modules.local/embedded-payment/src/ListLongIdPaymentRequestValidator.php#L56) | 56 | Put this in a const/enum or make it a constructor argument |
| [modules.local/list-session/src/ListSessionModule.php](modules.local/list-session/src/ListSessionModule.php#L62) | 62 | check if we still need this after Fetch command is implemented |
| [modules.local/payment-methods/inc/services.php](modules.local/payment-methods/inc/services.php#L311) | 311 | think about moving this to factories |
| [modules.local/payment-methods/inc/services.php](modules.local/payment-methods/inc/services.php#L359) | 359 | consider refactoring, these callbacks are almost the same. |
| [modules.local/settings/src/SettingsModule.php](modules.local/settings/src/SettingsModule.php#L758) | 758 | Remove/Refactor this when WC 9.7+ releases the revamped Payment Settings UX |
| [modules.local/wp/inc/factories.php](modules.local/wp/inc/factories.php#L18) | 18 | make our order_pay request be detected as a checkout in the same way as |
| [modules.local/wp/inc/services.php](modules.local/wp/inc/services.php#L288) | 288 | deal with the code duplication. Currently, we have the same logic |
| [modules.local/wp/inc/services.php](modules.local/wp/inc/services.php#L349) | 349 | check if this can return false because called too early |
| [modules.local/embedded-payment/src/PaymentFieldsRenderer/WidgetPlaceholderFieldRenderer.php](modules.local/embedded-payment/src/PaymentFieldsRenderer/WidgetPlaceholderFieldRenderer.php#L55) | 55 | Reuse another message here. The actual HPP flow uses a merchant-configurable string |
| [modules.local/list-session/src/Middleware/UpdatingMiddleware.php](modules.local/list-session/src/Middleware/UpdatingMiddleware.php#L110) | 110 | replace this with a better solution. |
| [modules.local/list-session/src/Middleware/UpdatingMiddleware.php](modules.local/list-session/src/Middleware/UpdatingMiddleware.php#L123) | 123 | Log errors during UPDATE |
| [modules.local/payment-methods/src/GatewayIconsRenderer/DynamicIconProvider.php](modules.local/payment-methods/src/GatewayIconsRenderer/DynamicIconProvider.php#L77) | 77 | logging |
| [modules.local/api/tests/php/Integration/Gateway/PaymentTestCase.php](modules.local/api/tests/php/Integration/Gateway/PaymentTestCase.php#L22) | 22 | replace 'inpsyde_payment_gateway.payoneer' with a mocked PaymentProcessor/ChargeCommand |
| [modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php](modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php#L87) | 87 | we never return anything else than 200 with empty body, so this assert is useless |
| [modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php](modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php#L203) | 203 | we never return anything else than 200 with empty body, so this assert is useless |
| [modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php](modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php#L306) | 306 | we never return anything else than 200 with empty body, so this assert is useless |
| [modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php](modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php#L435) | 435 | we never return anything else than 200 with empty body, so this assert is useless |
| [modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php](modules.local/webhooks/tests/php/Integration/Controller/PayoneerWebhooksControllerTest.php#L503) | 503 | we never return anything else than 200 with empty body, so this assert is useless |
