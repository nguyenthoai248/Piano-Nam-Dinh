(function (window, document, $) {

    $(function () {
        let showOnly = function (groupName, form) {
            let $sandboxFields = form.find('[group=sandbox_credentials]')
            let $liveFields = form.find('[group=live_credentials]')
            let $allGroupedFields = $sandboxFields.add($liveFields)

            $allGroupedFields.each(function (){
              $(this).attr('readonly', 'readonly')
            })
            form.find(`[group = ${groupName}]`).each(function (){
              $(this).removeAttr('readonly')
            })
        }

        let getCurrentGroupName = function (liveMode) {
            return liveMode
            ? 'live_credentials'
            : 'sandbox_credentials';
        }

        let $checkbox = $('#woocommerce_payoneer-checkout_live_mode')
        let $form = $checkbox.closest('form')

        $checkbox.on('change', function () {
            let liveMode = this.checked
            let $form = $(this).closest('form')

            showOnly(getCurrentGroupName(liveMode), $form)
        })

        showOnly(getCurrentGroupName($checkbox.prop('checked')), $form)

      const confirmReset = () => confirm(PayoneerData.i18n.confirmReset)
      /**
       * Initialize CodeMirror editor
       */
      document.querySelectorAll('textarea.code.css').forEach((input) => {
        window.wp.codeEditor.initialize(input);
      })

      /**
       * Initialize reset buttons
       */
      document.querySelectorAll('button[data-target]').forEach((button) => {
        button.addEventListener('click', (event)=>{
          const inputField=document.querySelector(button.dataset.target);
          inputField.dispatchEvent(new CustomEvent('reset', { detail: button.dataset.default }))
          event.preventDefault();
        });
      })
      /**
       * Generic reset logic for input fields
       */
      document.querySelectorAll('#mainform input').forEach((input) => {
        input.addEventListener('reset', (e) => {
          if(!confirmReset()){
            return;
          }
          input.value = e.detail
        })
      })
      /**
       * Generic reset logic for textarea fields
       */
      document.querySelectorAll('#mainform textarea').forEach((input) => {
        input.addEventListener('reset', (e) => {
          if(!confirmReset()){
            return;
          }
          input.innerHTML = e.detail

          /**
           * This is ugly because it technically does not belong here.
           * For now, ONLY CSS fields have reset capabilities to begin with, so it's okay.
           * But later, this should be moved to a place where it executes in a smarter fashion
           */
          $(input).next('.CodeMirror')[0].CodeMirror.setValue(input.value)
          $(input).next('.CodeMirror')[0].CodeMirror.refresh()
        })
      })
    })
})(top, document, jQuery);
