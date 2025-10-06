<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks;

use WP_REST_Request;
/**
 * Trigger specific action so that incoming webhook can be logged with request details.
 *
 * We trigger a specific action to provide arguments we want to have for logging.
 * The plugin logging system expects named arguments for action to log:
 * do_action('action_name', $arg1, $arg2), but in WordPress commonly
 * used actions with positional arguments:
 * do_action('action_name', ['arg1' => $arg1, 'arg2' => $arg2]).
 *
 * This class exists because we want to translate one into another and avoid changing existing
 * action arguments.
 */
class LogIncomingWebhookRequest
{
    /**
     * @var string
     */
    protected $securityHeaderName;
    /**
     * @param string $securityHeaderName
     */
    public function __construct(string $securityHeaderName)
    {
        $this->securityHeaderName = $securityHeaderName;
    }
    /**
     * @param WP_REST_Request $request
     *
     * @return void
     */
    public function __invoke(WP_REST_Request $request): void
    {
        /** @var array<string, string[]> $headers */
        $headers = $request->get_headers();
        $headers = $this->redactHeaders($headers);
        $stringifiedHeaders = $headers ? json_encode($headers) : 'empty';
        $queryParams = $request->get_query_params();
        $stringifiedQueryParams = $queryParams ? json_encode($queryParams) : 'empty';
        /** @var string|null $body */
        $body = $request->get_body();
        do_action('payoneer-checkout.log_incoming_notification', ['method' => $request->get_method(), 'queryParams' => $stringifiedQueryParams, 'bodyContents' => $body ?: 'empty', 'headers' => $stringifiedHeaders]);
    }
    /**
     * Remove sensitive data from headers.
     *
     * @param array<string, string[]> $headers
     *
     * @return array<string, string[]>
     */
    protected function redactHeaders(array $headers): array
    {
        //underscore is for psalm to not complain about unused variable
        foreach ($headers as $headerName => $_headerValue) {
            if ($headerName === WP_REST_Request::canonicalize_header_name($this->securityHeaderName)) {
                $headers[$headerName] = ['*****'];
            }
        }
        return $headers;
    }
}
