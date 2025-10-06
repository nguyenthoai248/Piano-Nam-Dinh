<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Email;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\SystemReportDataDTO;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\SystemReportParamsDTO;
/**
 * Responsible for sending the system report via email.
 */
interface SystemReportEmailSenderInterface
{
    /**
     * Sends the system report via email.
     *
     * @param SystemReportDataDTO $reportData The report data to send.
     * @param SystemReportParamsDTO $params The request parameters.
     *
     * @return bool True if email was sent successfully
     */
    public function sendReport(SystemReportDataDTO $reportData, SystemReportParamsDTO $params): bool;
}
