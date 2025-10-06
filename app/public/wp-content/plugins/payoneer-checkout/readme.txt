=== Payoneer Checkout ===
Contributors: payoneercheckout, inpsyde
Tags: payment, woocommerce, checkout
Requires at least: 5.3
Tested up to: 6.8.2
Requires PHP: 7.4
Stable tag: 3.5.1
License: MPL-2.0
License URI: https://www.mozilla.org/en-US/MPL/2.0/

Payoneer Checkout for WooCommerce - Build beautiful checkout flows + manage payments in one place 

== Description ==

Payoneer Checkout is the next generation of payment processing platforms, giving merchants around the world the solutions and direction they need to succeed in today’s hyper-competitive global market. 

We’re talking, out of the box payments pages, major payment methods supported, critical currencies provisioned, fraud prevention, chargeback management, developer tools, multi-store support, analytics capabilities, supplier payment options, fund withdrawal from your local account, virtual and physical cards, capital advances and many more.  

All managed from one place. 

Be like the brands you look up to: offer frictionless payment experiences through Payoneer Checkout, that make customers want to buy. 

* Increase acceptance rates 
* Reduce cart abandonment 
* Speed up your settlement times 
* Save on foreign exchange fees 
* Reduce fraudulent payments 
* Ensure store compliance 

= Why are global merchants switching to Payoneer Checkout? =

* Over 17 years’ experience delivering high quality financial solutions at budget prices
* The world’s biggest brands, like Ebay, Amazon and Airbnb, trust us… 
* … And smallest, with over 5 million customers and counting around the globe 
* Transparent pricing now and forever 
* Cast iron compliance: we are regularly audited by the world’s top financial institutions 
* Security built into every single transaction, protecting you 365 days of the year 
* 24/7 support, in your local language, delivered by business experts 
* Endorsements by Forbes, Bloomberg, Reuters and many more 
 
= Reach customers in 190+ countries worldwide = 

Our global banking and payment networks stretch around the world so we can support you and your customers no matter where they are.

* 24/7 customer support in 35+ languages
* Available in 200+ markets
* Supporting 120+ currencies
* Fee free settlement of funds into USD, JPY, GBP, HKD and EUR
* Responsive design for mobile and desktop
* Protecting every payment with smart fraud detection technology

== Frequently Asked Questions ==

= Where is documentation located =

[Connect WooCommerce](https://checkoutdocs.payoneer.com/docs/integrate-with-woocommerce "Payoneer Checkout for WooCommerce documentation")

== Screenshots ==

== Changelog ==
= [3.5.1] - 2025-09-25 =
* Fixed
    * Fatal error on plugin activation

= [3.5.0] - 2025-09-24 =
* Added
    * Plugin compatibility with Checkout WebSDK and Stripe Connect.
    * Handling of asynchronous refund flow.
    * Admin view UX updates in order to handle async refund flow and notifications.
    * Extended support for additional payment methods for updated Checkout solution: Apple Pay, Google Pay, Afterpay, Klarna, Affirm, P24, EPS, Bancontact, iDEAL & Multibanco.

* Fixed
    * Security vulnerability (CVE-2025-58795)

= [3.4.0] - 2025-07-02 =
* Added:
  * Automated generation of System Status Reports and Logfiles to assist support processes.

* Fixed:
  * Resolved payment rejection issue caused by an incorrect Security header.
  * Fixed compatibility issue with WordPress Multisite.
  * Addressed an incompatibility with WooCommerce Multilingual & Multicurrency that caused the _Pay for Order_ page to break.
  * Ensured available payment methods in the LIST are correctly updated after changes to the total amount.

* Changed:
  * Adjusted value handling to not include customer.number as a required value in SDK.
  * Removed the ProcessingModel entity for MoR from the SDK.

= [3.3.2] - 2025-06-02 =
* Fixed:
  * Issue allowing checkout form to be submitted with invalid cards fields
  * Issue with additional payment method icons being displayed on Payoneer Checkout at checkout and block cart page
  * PHP 8+ errors caused during API calls after 3.3.0 release
  * PHP 7.4 errors caused during API calls after 3.3.0 release
* Changed:
  * Repeated payment attempts no longer generate new orders. The original order is now reused, with its status set to On hold after checkout and updated to Failed if the payment is declined.

= [3.3.1] - 2025-04-02 =
* Added
    * SRI integrity hash (security enhancement for PSI/DSS 4.0 compatibility)

= [3.3.0] - 2025-03-24 =
* Added
  * Added support for blocks checkout, compatible with both hosted and embedded payment flows
  * Payment method logos are now displayed dynamically, based on available payment networks

* Fixes & Removals
  * Removed static appearance configuration in plugin settings for payment method logos (now replaced by added support of dynamic payment methods logos)
  * Fixed an issue where card fields became unresponsive and the checkout design broke after a refused payment on checkout
  * Resolved a conflict with the Zettle plugin, improving overall compatibility

= [3.2.4] - 2024-12-11 =
* Fixed:
  * Fix incorrect webSDK integration
  * Reduce call UPDATE on less important fields
  * Remove modules.local directory in the public version of the plugin
  * Fix order status change in pre-dispute flow
  * Added missing translations

= [3.2.3] - 2024-10-01 =
* Fixed
  * payment session ID validation
  * missing string translations

= [3.2.2] - 2024-08-22 =
* Fixed
  * Unexpected webSDK payment button presence

= [3.2.1] - 2024-08-12 =
* Fixed:
  * Incorrect order status on payment after expired session in hosted flow
  * Icons | incorrect sizes in a popular theme

= [3.2.0] - 2024-07-24 =
* Added
  * Diners & Discover support
* Fixed
  * order status updating on HPOS
  * long payment method titles rendering
  * order status when 2 orders are created from 2 tabs
  * empty payment method titles would render default value

= [3.1.0] - 2024-07-17 =
* Added
  * Afterpay support
* Fixed
  * auto-selecting payment method in fallback mode caused by network failure
  * Compatibility | WordPress 6.6
  * Compatibility | Displaying double Place Order button

= [3.0.1] - 2024-07-09 =
* Fixed
    * Removed redundant error message when payment method is hidden

= [3.0.0] - 2024-07-03 =

* Changed
  * replacing deprecated payment widget with new Checkout SDK
  * plugin configuration page (foundation for multiple payment methods)
  * removed CSS configuration
  * removed MoR support
* Added
  * dedicated payment containers for embedded and hosted flows
* Fixed
  * Compatibility | with empty $_SERVER['HTTP_HOST']
  * Compatibility | early checkout page detection
  * Fallback to hosted flow for registered customer
  * List session is created when payment method is disabled

= [2.0.0] - 2024-05-02 =
* Changed
  * Dependency scoping to improve compatibility, internal API changed
* Added
  * Payoneer Checkout setup information to WooCommerce System Report
  * Displaying 1 icon set in Embedded mode at checkout page
* Fixed
  * AJAX Search Pro incompatibility
  * Fluid Checkout for WooCommerce incompatibility
  * Creating redundant LIST sessions in edge cases
  * wc_get_template hook unexpected types handling

= [1.6.0] - 2024-03-21 =
* Added
  * Hosted payment page v5 in Hosted flow
  * Embedded and hosted payment flow unification
  * Payment session update on ZIP change
  * New American Express logo config message
* Fixed
  * Triggering firewall by stripping HTML elements from product descriptions
  * Missing download link for digital products on order-received page
  * Missing style.hostedVersion in an edge case
  * Incorrect message for expired payment session
  * WooCommerce 5.0.0 compatibility

= [1.5.1] - 2023-11-29 =
* Added
  * JCB – configurable logo
* Fixed
  * Compatibility with WooCommerce 5.0
  * Display warning if WooCommerce is disabled

= [1.5.0] - 2023-09-14 =
* Added:
  * Analytics | plugin installation
  * Analytics | customers conversion at checkout page and payment acceptance

= [1.4.2] - 2023-07-12 =
* Fixed:
  * Fatal error on checkout with WooCommerce < 6.6.0

= 1.4.1 - 2023-07-03 =
* Fixed:
  * Icons order on checkout
  * Credentials validation for pure MoR merchants

= 1.4.0 - 2023-06-05 =
* Added:
  * American Express - configurable logo
  * Merchant of Record - improved error messages
  * Payment method displays only with valid merchant account configuration
* Fixed:
  * User registration / login is blocked on my account and checkout page
  * Payment fails when a user tries to register during checkout

= 1.3.2 - 2023-05-22 =
* Fixed:
  * Send customer shipping/billing state to gateway

= 1.3.1 - 2023-05-02 =
* Added:
  * Compatibility with plugins that redeclare WordPress global functions (BuddyBoss, ...)
  * Workaround for WordPress issue for callingremove_action in action processing. Compatibility with SalesGen
* Fixed:
  * CSS with single quote parsing
  * Frontend global listeners stay hooked after failed payment
  * Expired session handling required checkout page reload

= 1.3.0 - 2023-04-06 =
* Added:
  * Merchant of Record
* Fixed:
  * blocked checkout page after second 3D Secure payment (critical)
  * creating redundant payment session after fallback
  * missing payment widget update after shipping country update

= 1.2.0 - 2023-03-14 =
* Add: Block live mode until first status notification is received
* Add: Automatically recover from some error scenarios during checkout
* Add: Include version number in logger service
* Change: Fallback to hosted payment mode if payment widget fails to load
* Change: Avoid potential duplicate transaction IDs
* Change: Advertise WooCommerce system/integration type to Payoneer API
* Change: Improve wording of some settings
* Fix: Refresh payment widget after change of shipping country

= 1.1.0 - 2022-12-22 =
* Add: Display banner with onboarding assistant after initial plugin activation
* Change: Declare compatibility with WooCommerce High Performance Order Storage
* Change: Never let exceptions bubble up just because WP_DEBUG is set
* Fix: Redirect URL from settings page was wrong in multisite installations
* Fix: Discounts of individual line items not applied when generating product list
* Fix: Wrong order note after partial refund on webhook

= 1.0.0 - 2022-11-02 =
* Fix: Improve checkout behaviour when run alongside WooCommerce PayPal Payments
* Fix: Special characters are no longer escaped when saving custom CSS
* Fix: Correctly transfer coupon, tax and shipping items in API calls
* Fix: Correctly transfer customer first & last name in API calls
* Fix: Configuration changes sometimes weren't immediately reflected after saving the settings page
* Change: Removed "basic css" settings in favor of greatly improved custom css settings
* Change: Declare compatibility with WordPress 6.1
* Change: Improved error message when manually cancelling payment in hosted mode
* Change: No longer block the full UI during checkout operations
* Change: Update minimum required WooCommerce version
* Change: Remove testing code from generated zip files
* Add: "Test: " prefix prepended to payment method title when test mode is active
* Add: Link to documentation from payment gateway settings
* Add: Provide default "custom CSS" and the ability to revert to it

= 0.6.0 - 2022-10-19 =
* Fix conflict with CoCart plugin
* Fix rare duplicate error message when entering checkout
* Fix: No longer bootstrap payment gateway when it is disabled in woocommerce payment settings
* Fix: Make psalm & phpcs inspect additional folders
* Changed embedded payment mode to "client-side CHARGE" flow
* Changed: Initialize WebSDK with dedicated Pay button that is toggled upon gateway selection
* Added: Log all notifications
* Added registration/saving of payment methods
* Added: Use gateway description as placeholder for hosted flow

= 0.5.2 - 2022-09-19 =
* Fix checkout failure without JS.
* Fix 'LIST URL mismatch' checkout error with WooCommerce `5.6.2` and below.

= 0.5.1 - 2022-09-06 =
* No longer use `WC_Session_Handler::get_customer_unique_id` as it is only available from WC 5.3+

= 0.5.0 - 2022-08-30 =
* Fix failed payment try after failed 3DS challenge in hosted mode
* Fix broken LIST expiration handling
* Fix creating redundant LIST sessions

= 0.4.2 - 2022-08-08 =
* Fix conflicts with plugins and themes changing checkout page.
* Fix checkout for countries without required postal code.

= 0.4.1 - 2022-08-08 =
* Official Visa and Mastercard icons are used.

= 0.4.0 - 2022-07-29 =
* Fixed type error in checkout data handling when `CoCart` plugin is active
* Changed default payment widget CSS so it is no longer too tall in some environments
* Always (and only) used billing phone number when sending customer details
* Provided information about merchant's system (WooCommerce) when creating List session
* Added Credit card icons next to payment gateway title
- Added ability to switch to "Hosted Payment Page" flow ("hosted mode")
- Added placeholder message and additional error handling during LIST session creation in embedded mode

= 0.3.0 - 2022-06-27 =
* Added missing translations for payment method title and description.
* Added message to distinguish between refunds type on the order page.
* Fixed payment on the Pay for order page.
* Fixed transaction link for MRS_* merchants.
* Fixed potential problem with executing some webhooks twice.
* Fixed invalid CSS when defaults settings are used.
* Fixed loading checkout assets when payment gateway is disabled.
* Fixed general error message instead of exact one for specific payment failure cases.

= 0.2.1 - 2022-05-25 =
### Fixed
* Fix: Unpaid orders also show a working transation ID on the orders page
* Fix: Removed giant error message during checkout that coiuld appear in rare cases
* Fix: LIST session is only stored on the order if it was paid for with our gateway
* Fix: Checkout widget handles removal of payment gateway during checkout more gracefully
* Change: Gateway now verifies that the checkout has been made via the checkout widget
* Change: Checkout widget now has a placeholder message until it has initialized

= 0.2.0 - 2022-05-12 =
* Added internationalization of errors.
* Fixed admin order transaction link when the order completed on webhook.
* Fixed checkout failure if no phone provided.


= 0.1.0 - 2022-04-22 =
* Added Payoneer Checkout payment gateway.
* Added card payments support.
* Added payment widget customization feature.
* Added support for asynchronous status notifications.
* Added support for refunds.

== Upgrade Notice ==

= 0.5.2 =

Please update to get the latest features.

