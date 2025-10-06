document.addEventListener(
  'DOMContentLoaded',
  function () {
    if (typeof checkoutList === 'undefined') {
      return;
    }

    const checkoutForm = jQuery('form.checkout')
    let widgetContainer = [];

    function hasWidgetContainer() {
      widgetContainer = jQuery('#' + PayoneerData.paymentFieldsContainerId);
      return widgetContainer.length > 0;
    }

    function isPayForOrder() {
      return PayoneerData.isPayForOrder === '1';
    }

    function isWidgetInitialized() {
      const widgetContainer = jQuery('#' + PayoneerData.paymentFieldsContainerId + ' .op-payment-widget-container');
      if (!widgetContainer.length) {
        return false;
      }
      // Now we check if the widget is displaying some errors instead of payment fields
      const widgetErrors = jQuery('.GLOBAL_ERROR', widgetContainer);
      return widgetErrors.length === 0;
    }

    function debounce(func, timeout = 300) {
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
          func.apply(this, args);
        }, timeout);
      };
    }

    function isPayoneerGatewaySelected() {
      var selectedPaymentMethod = jQuery('.woocommerce-checkout input[name="payment_method"]:checked').attr('id');
      return selectedPaymentMethod === 'payment_method_payoneer-checkout'
    }

    function showWooCommercePlaceOrderButton() {
      jQuery('#payoneer_place_order').prop("disabled", true).hide();
      jQuery('#place_order').prop("disabled", false).show();
    }

    function showPayoneerPlaceOrderButton() {
      jQuery('#place_order').prop("disabled", true).hide();
      jQuery('#payoneer_place_order').prop("disabled", false).show();
    }

    function togglePlaceOrderButtons() {
      isPayoneerGatewaySelected() ? showPayoneerPlaceOrderButton() : showWooCommercePlaceOrderButton();
    }

    function submitOrderPayViaAjax(success, error) {
      jQuery.ajax({
        type: 'POST',
        url: wc_checkout_params.ajax_url,
        xhrFields: {
          // This is important. We need the session cookie to access the LIST in the back-end
          withCredentials: true
        },
        dataType: 'json',
        data: {
          action: 'payoneer_order_pay',
          fields: jQuery('#order_review').serialize(),
          params: (new URL(document.location)).searchParams.toString()
        },
        success: function (data) {
          success(data)
        },
        error: function (data, textStatus, errorThrown) {
          error(data)
          /**
           * Reloading enables us to see error messages added via wc_add_notice()
           */
          window.location.reload();
        }
      })
    }

    /**
     * We need to hook into global events emitted by WooCommerce for our charge to execute.
     * This is only supposed to happen once, but we need to know if it succeeded or errored out.
     * Since we don't want to leave dangling event handlers executing unneeded logic after finishing,
     * The callbacks are guarded by a common 'hasRun' bool, which ensures that only one of the three
     * routes can be taken.
     *
     * @param resolve
     * @param onSuccess optionally execute a callback after the order has been validated
     * @param onError optionally execute a callback after the order failed to validate
     */
    function createPromiseResolver(resolve, onSuccess, onError) {
      let hasRun = false;
      checkoutForm.one('checkout_place_order_success', function (event, result) {
        if (hasRun) {
          return;
        }
        hasRun = true
        resolve(true)
        onSuccess && onSuccess()
        return false;
      });
      jQuery(document.body).one('checkout_error', function (event, result) {
        if (hasRun) {
          return;
        }
        hasRun = true
        onError && onError()
        resolve(false)
      });
      /**
       * This is just a safety net to release the checkout form eventually if something fails
       */
      window.setTimeout(function () {
        if (hasRun) {
          return;
        }
        hasRun = true
        onError && onError()
        resolve(false)
      }, 20000);
    }

    /**
     * Repeated calls to 'checkoutList' can result in missing iFrames for reasons beyond our control
     * To be on the safe side, we create a debounced version of the actual init logic
     * so it can only run once every 300ms.
     * (Manual testing showed it happens if called twice with a delay <100ms)
     * @type {(function(...[*]): void)|*}
     */
    const initWidget = debounce(function () {
      /**
       * Getting this data and initializing payment widget should happen everytime on updated_checkout event.
       * This is because LIST session might be replaced with a new one because of expiring or other reasons.
       * On each updated_checkout event the field containing identifier is updated with WC fragments.
       * So, by getting LIST identifier and re-initializing payment widget each time we can be sure we
       * have the newest session id.
       */

      const listIdContainer = jQuery('#' + PayoneerData.listUrlContainerId);
      const listUrl = listIdContainer.val();
      // The checkout form has differing selectors on regular checkout and 'pay-for-order'
      const formSelector = PayoneerData.isPayForOrder === '0' ? 'form.checkout' : '#order_review'

      const payload = {
        listUrl: listUrl,
        fullPageLoading: false,
        payButton: PayoneerData.payButtonId,
        widgetCssUrl: PayoneerData.widgetCssUrl,
        cssUrl: PayoneerData.cssUrl,
        onBeforeCharge: async () => {
          const chargeAttempt = new Promise((resolve) => {
            let hostedModeFlag = jQuery('input[name=' + PayoneerData.hostedModeOverrideFlag + ']');
            hostedModeFlag.prop('disabled', true)
            if (PayoneerData.isPayForOrder) {
              submitOrderPayViaAjax(
                () => resolve(true),
                () => resolve(false)
              )
            } else {
              createPromiseResolver(resolve,null,function(){
                hostedModeFlag.prop('disabled', false)
              })
              jQuery(formSelector).submit();
            }
          })

          return await chargeAttempt;
        },
        onBeforeServerError: async (errorData) => {
          const serverError = new Promise((resolve) => {
            if(isPayForOrder()) {
              const url = new URL(document.location);
              url.searchParams.set(PayoneerData.payOrderErrorFlag, true);
              window.location.href = url.toString();
              resolve(false);
            }

            let onErrorFlag = jQuery('input[name=' + PayoneerData.onErrorRefreshFragmentFlag + ']');
            onErrorFlag.prop('value', true)
            console.log('onBeforeServerError', errorData)
            // Clear the widget container so that we re-init after fragment update
            jQuery('#' + PayoneerData.paymentFieldsContainerId).empty();
            jQuery(document.body).trigger('update_checkout');
            resolve(false)
          })

          return await serverError;
        }
      };
      if (!listUrl || listUrl === '') { // Prevent unnecessary init with empty LIST Url
        return;
      }
      destroyWidget();
      console.log('Initializing Payoneer payment widget', payload);
      widgetContainer.empty().checkoutList(payload);
    });

    /**
     * Cannot use native event listener for events dispatched by jQuery
     * https://github.com/jquery/jquery/issues/3347
     */
    jQuery(document.body).on('payment_method_selected', function () {
      isPayoneerGatewaySelected() && hasWidgetContainer() && !isWidgetInitialized() && initWidget();
      togglePlaceOrderButtons();
    });

    jQuery(document.body).on('updated_checkout', function () {
      hasWidgetContainer() && !isWidgetInitialized() && isPayoneerGatewaySelected() && initWidget();
      togglePlaceOrderButtons();
    });

    jQuery('#payoneer_place_order').on('click', function (e) {
      e.preventDefault();
    })

    // No fragment update on 'order-pay'. So we manually initialize our widget
    if (isPayForOrder() && isPayoneerGatewaySelected() && hasWidgetContainer() && !isWidgetInitialized()) {
      initWidget();
    }

    /**
     * To be extra sure that our button does not trigger the native form submission,
     * we preventDefault() explicitly
     */
    jQuery('#payoneer_place_order').on('click', function (e) {
      e.preventDefault();
    })
    togglePlaceOrderButtons();

  },
  false,
);
