<?php
declare(strict_types=1);

namespace DmiRud\Dropday\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use DmiRud\Dropday\Model\Config;
use DmiRud\Dropday\Model\Export\StatusProvider;

/**
 * Update export status based on order status.
 */
class UpdateOrderExportStatusObserver implements ObserverInterface
{
    public function __construct(private readonly Config $config, private readonly StatusProvider $statusProvider)
    {
    }

    /**
     * Set DropDay export status flag
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): self
    {
        /** @var Order $order */
        if (!($this->config->isEnabled() and $order = $observer->getEvent()->getOrder())) {
            return $this;
        }

        $order->setData(Config::ORDER_FIELD_EXPORT_STATUS, $this->statusProvider->get($order));

        return $this;
    }
}
