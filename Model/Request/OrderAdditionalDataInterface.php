<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Plugin\Dropday\OrderAutomation\Model;

use Magento\Sales\Api\Data\OrderInterface;

interface OrderAdditionalDataInterface
{
    /**
     * @param OrderInterface $order
     * @return array
     */
    public function getParams(OrderInterface $order): array;
}