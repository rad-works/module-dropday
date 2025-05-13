<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Model\Export;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use RadWorks\Dropday\Model\Config;
use RadWorks\Dropday\Model\DropdayAdapter;
use RadWorks\Dropday\Model\ResourceModel\SaveOrderExportStatus;

class OrderService
{
    public function __construct(
        private readonly Config                   $config,
        private readonly DropdayAdapter           $apiAdapter,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SaveOrderExportStatus    $saveOrderExportStatus,
        private readonly SearchCriteriaBuilder    $searchCriteriaBuilder
    )
    {
    }

    /**
     * Handle order exporting/creation via Dropday API
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function exportOrder(OrderInterface $order): bool
    {
        if ($result = $this->apiAdapter->createOrder($order)) {
            $createdAt = (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT);
            $data = [
                Config::ORDER_FIELD_EXPORT_STATUS => Config::ORDER_FIELD_EXPORT_STATUS_COMPLETE,
                Config::ORDER_FIELD_EXPORT_CREATED_AT => $createdAt
            ];
        }

        $this->saveOrderExportStatus->execute(
            (int)$order->getEntityId(),
            $data ?? [Config::ORDER_FIELD_EXPORT_STATUS => Config::ORDER_FIELD_EXPORT_STATUS_FAILED]
        );

        return $result;
    }

    /**
     * Get list of orders for export
     *
     * @return OrderSearchResultInterface
     */
    public function getList(): OrderSearchResultInterface
    {
        $skipStates = [Order::STATE_CANCELED, Order::STATE_CLOSED, Order::STATE_HOLDED];
        $searchCriteria = $this->searchCriteriaBuilder
            //Order must not be closed or cancelled or holded.
            ->addFilter(OrderInterface::STATE, $skipStates, 'nin')
            //Order has been marked for import.
            ->addFilter(Config::ORDER_FIELD_EXPORT_STATUS, $this->config->getAllowedExportStates(), 'in')
            //For old orders, order must not have reference.
            ->addFilter(Config::ORDER_FIELD_EXPORT_REFERENCE, null, 'null');
        if ($minutes = $this->config->getExportDelayInMinutes()) {
            $searchCriteria->addFilter(
                OrderInterface::CREATED_AT,
                (new \DateTime())->modify(sprintf('-%d minutes', $minutes)),
                'lteq'
            );
        }

        return $this->orderRepository->getList($searchCriteria->create());
    }
}