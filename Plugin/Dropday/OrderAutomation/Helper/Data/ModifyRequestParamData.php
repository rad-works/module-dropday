<?php
declare(strict_types=1);

namespace DmiRud\Dropday\Plugin\Dropday\OrderAutomation\Helper\Data;

use Dropday\OrderAutomation\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

/**
 * Customize API request params:
 * - (legacy customization) use cost instead of price
 */
class ModifyRequestParamData
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * Modify request data
     *
     * @param Data $subject
     * @param array $result
     * @param Order $order
     * @return array
     */
    public function afterGetOrderRequestData(Data $subject, array $result, Order $order): array
    {
        foreach (($result['products'] ?? []) as $index => $item) {
            try {
                $product = $this->productRepository->get($item['reference']);
                //Replace price with the cost
                $result['products'][$index]['price'] = (float)$product->getCost();
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $result;
    }
}