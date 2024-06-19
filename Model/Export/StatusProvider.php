<?php
declare(strict_types=1);

namespace DmiRud\Dropday\Model\Export;

use Magento\Sales\Model\Order;
use DmiRud\Dropday\Model\Config;

/**
 * Provides export status based on order's data
 */
class StatusProvider
{
    /**
     * Skip export for orders in these states
     */
    private const SKIP_ORDER_STATES = [Order::STATE_CANCELED, Order::STATE_CLOSED];

    public function __construct(private readonly Config $config)
    {
    }

    /**
     * Get export status
     *
     * @param Order $order
     * @return int
     */
    public function get(Order $order): int
    {
        //Order has been exported.
        if ($order->getData(Config::ORDER_FIELD_EXPORT_REFERENCE)) {
            return Config::ORDER_FIELD_EXPORT_STATUS_COMPLETE;
        }

        $exportStatus = $order->getData(Config::ORDER_FIELD_EXPORT_STATUS);
        //Canceled or closed orders can't be in export's "pending" status.
        if (
            in_array($exportStatus, $this->config->getAllowedExportStates())
            &&
            in_array($order->getState(), self::SKIP_ORDER_STATES)
        ) {
            return Config::ORDER_FIELD_EXPORT_STATUS_INITIAL;
        }

        //Assign status based on rules.
        if ($this->matchRules($order)) {
            return Config::ORDER_FIELD_EXPORT_STATUS_PENDING;
        }

        return Config::ORDER_FIELD_EXPORT_STATUS_INITIAL;
    }

    /**
     * Determines if an order matches the export rules
     *
     * @param Order $order
     * @return bool
     */
    private function matchRules(Order $order): bool
    {
        if (!$order->getPayment()) {
            return false;
        }

        $status = $order->getState() . '_' . $order->getStatus();
        foreach ($this->config->getExportRules() as $method => $statuses) {
            if (!($method == $order->getPayment()->getMethod() && in_array($status, $statuses))) {
                continue;
            }

            return true;
        }

        return false;
    }
}