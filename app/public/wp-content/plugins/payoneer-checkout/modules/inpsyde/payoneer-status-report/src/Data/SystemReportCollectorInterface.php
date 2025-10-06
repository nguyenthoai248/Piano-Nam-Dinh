<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data;

/**
 * Service definition for the report data collector.
 *
 * The collector is responsible for compiling a report-data object, based on a report configuration
 * which is prepared by the REST endpoint handler. The resulting report-data object will be used by
 * the email sender to deliver the report.
 */
interface SystemReportCollectorInterface
{
    /**
     * Collect system information based on provided parameters.
     *
     * @param SystemReportParamsDTO $params Report configuration details.
     *
     * @return SystemReportDataDTO The collected report data
     */
    public function collect(SystemReportParamsDTO $params): SystemReportDataDTO;
}
