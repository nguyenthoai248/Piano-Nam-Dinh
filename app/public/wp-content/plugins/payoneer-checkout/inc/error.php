<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Validation\Exception\ValidationFailedExceptionInterface;
return (static function () {
    $formatValidationError = static function ($error) {
        if ($error instanceof \Throwable) {
            return $error->getMessage();
        }
        return (string) $error;
    };
    return static function (\Throwable $exception) use ($formatValidationError) {
        $message = $exception->getMessage();
        /**
         * We add more developer-friendly error output if the PAYONEER_DEBUG
         * env var is set
         */
        if ((bool) \getenv('PAYONEER_DEBUG')) {
            while ($previousException = $exception->getPrevious()) {
                $message .= '</br>' . $previousException->getMessage();
                $exception = $previousException;
            }
        }
        if ($exception instanceof ValidationFailedExceptionInterface) {
            $errors = [];
            foreach ($exception->getValidationErrors() as $validationError) {
                $errors[] = $formatValidationError($validationError);
            }
            $message .= '</br>' . \implode('</br>', $errors);
        }
        \add_action('all_admin_notices', static function () use ($message) {
            $class = 'notice notice-error';
            \printf(
                '<div class="%1$s"><h4>%2$s</h4><p>%3$s</p></div>',
                \esc_attr($class),
                /* translators: The entire plugin failed to bootstrap. This message is only visible to admins and will be accompanied by a list of errors */
                \esc_html__('Payoneer Checkout failed to initialize', 'payoneer-checkout'),
                \wp_kses_post($message)
            );
        });
    };
})();
