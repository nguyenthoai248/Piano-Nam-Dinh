<?php

/**
 * The status report renderer.
 *
 * @package Inpsyde\PayoneerForWoocommerce\StatusReport
 */
declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport;

/**
 * @psalm-type StatusReportItem = array{
 *      label: string,
 *      exported_label: string,
 *      value: string,
 *      description?: string
 *    }
 */
class Renderer
{
    /**
     * It renders the status report content.
     *
     * @param string $title The title.
     * @param StatusReportItem[]  $items The items.
     * @return string
     */
    public function render(string $title, array $items): string
    {
        ob_start();
        ?>
        <table class="wc_status_table widefat" id="status">
            <thead>
            <tr>
                <th colspan="3" data-export-label="<?php 
        echo esc_attr($title);
        ?>">
                    <h2><?php 
        echo esc_html($title);
        ?></h2>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php 
        foreach ($items as $item) {
            ?>
                <tr>
                    <td data-export-label="<?php 
            echo esc_attr($item['exported_label'] ?? $item['label']);
            ?>">
                        <?php 
            echo esc_attr($item['label']);
            ?>
                    </td>
                    <td class="help"><?php 
            echo !empty($item['description']) ? wp_kses_post(wc_help_tip($item['description'])) : '';
            ?></td>
                    <td><?php 
            echo wp_kses_post($item['value']);
            ?></td>
                </tr>
                <?php 
        }
        ?>
            </tbody>
        </table>
        <?php 
        return (string) ob_get_clean();
    }
}
