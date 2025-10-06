<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice;

use Exception;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller\WpRestApiControllerInterface;
use WP_REST_Request;
use WP_REST_Response;
class AdminNoticeEndpointController implements WpRestApiControllerInterface
{
    public function checkPermission(string $capability = ''): bool
    {
        if ($capability) {
            return current_user_can($capability);
        }
        return is_user_logged_in();
    }
    public function handleWpRestRequest(WP_REST_Request $request): WP_REST_Response
    {
        $dismissType = $this->getDismissType($request);
        $dismissId = $this->getDismissId($request);
        if (empty($dismissType)) {
            return new WP_REST_Response(['success' => \false, 'message' => 'Missing required parameters: type'], 400);
        }
        AdminNoticeHooks::log($dismissType, $dismissId);
        try {
            AdminNoticeHooks::dismiss($dismissType, $dismissId);
            return new WP_REST_Response(['success' => \true], 200);
        } catch (Exception $exception) {
            return new WP_REST_Response(['success' => \false, 'message' => $exception->getMessage()], 500);
        }
    }
    private function getDismissType(WP_REST_Request $request): string
    {
        $type = $request->get_param('type');
        if ($type) {
            return sanitize_key((string) $type);
        }
        return '';
    }
    private function getDismissId(WP_REST_Request $request): int
    {
        $id = (int) $request->get_param('id');
        if ($id > 0) {
            return $id;
        }
        return 0;
    }
}
