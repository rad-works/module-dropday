<?php
declare(strict_types=1);

namespace DmiRud\Dropday\Ui\Component\Listing\Column\DropDayExport;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use DmiRud\Dropday\Model\Config;

/**
 * Renders order export status grid column
 */
class Status extends Column
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ContextInterface         $context,
        UiComponentFactory       $uiComponentFactory,
        array                    $components = [],
        array                    $data = []
    )
    {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare order status values
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        foreach (($dataSource['data']['items'] ?? []) as $index => $item) {
            $order = $this->orderRepository->get($item["entity_id"]);
            $status = $order->getData(Config::ORDER_FIELD_EXPORT_STATUS);
            $data = &$dataSource['data']['items'][$index];
            if ($status == Config::ORDER_FIELD_EXPORT_STATUS_FAILED) {
                $data[$this->getData('name')] = __('Failed');
                continue;
            }

            if (
                $status == Config::ORDER_FIELD_EXPORT_STATUS_COMPLETE
                ||
                $order->getData(Config::ORDER_FIELD_EXPORT_REFERENCE)
            ) {
                $data[$this->getData('name')] = __('Complete');
                continue;
            }

            if ($status == Config::ORDER_FIELD_EXPORT_STATUS_INITIAL) {
                $data[$this->getData('name')] = __('None');
                continue;
            }

            $data[$this->getData('name')] = __('Pending');
        }

        return $dataSource;
    }
}