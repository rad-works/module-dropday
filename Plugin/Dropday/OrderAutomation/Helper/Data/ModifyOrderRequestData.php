<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Plugin\Dropday\OrderAutomation\Helper\Data;

use Dropday\OrderAutomation\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use RadWorks\Dropday\Plugin\Dropday\OrderAutomation\Model\OrderAdditionalDataInterface;

/**
 * Customize API request params:
 * - (legacy customization) use cost instead of price
 * - replace configurable product data with the simple product data
 */
class ModifyOrderRequestData
{
    /**
     * @var ProductRepositoryInterface $productRepository
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var OrderAdditionalDataInterface $additionalParams
     */
    private OrderAdditionalDataInterface $additionalParams;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param OrderAdditionalDataInterface $additionalParams
     */
    public function __construct(ProductRepositoryInterface $productRepository, OrderAdditionalDataInterface $additionalParams)
    {
        $this->productRepository = $productRepository;
        $this->additionalParams = $additionalParams;
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
        $productsData = &$result['products'];
        foreach ($productsData as $index => $data) {
            if ($product = $this->getProduct($data['reference'], $order->getStoreId())) {
                //Replace price with the cost
                $productsData[$index]['price'] = (float)$product->getCost();
            }
        }

        /**
         * Replace configurable product data with the simple product data
         */
        foreach ($order->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                continue;
            }

            foreach ($productsData as $index => $data) {
                if ($item->getSku() !== $data['reference']) {
                    continue;
                }

                $productsData[$index]['external_id'] = $item->getProductId();
                $productsData[$index]['reference'] = $item->getSku();
                if ($name = $this->getProduct($data['reference'], $order->getStoreId())?->getName()) {
                    $productsData[$index]['name'] = $name;
                }
            }
        }

        return array_merge($result, $this->additionalParams->getParams($order));
    }

    /**
     * Get product
     *
     * @param string $sku
     * @param int|string|null $storeId
     * @return ProductInterface|null
     */
    private function getProduct(string $sku, int|string $storeId = null): ?ProductInterface
    {
        try {
            return $this->productRepository->get($sku, storeId: $storeId);
            //Replace price with the cost
        } catch (NoSuchEntityException) {
            return null;
        }
    }
}
