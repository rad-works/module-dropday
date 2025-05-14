<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Swissup\CheckoutFields\Api\Data\FieldInterface as CheckoutFieldInterface;
use Swissup\CheckoutFields\Model\ResourceModel\Field\Grid\Collection;
use Swissup\CheckoutFields\Model\ResourceModel\Field\Grid\CollectionFactory;

class SwissupCheckoutFields implements OptionSourceInterface
{
    /**
     * @var CollectionFactory $collectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var array $options
     */
    private array $options = [];

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {

        if (!$this->options) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            /** @var CheckoutFieldInterface $checkoutField */
            foreach ($collection->load()->getItems() as $checkoutField) {
                $this->options[] = [
                    'value' => $checkoutField->getAttributeCode(),
                    'label' => $checkoutField->getFrontendLabel()
                ];
            }
        }

        return $this->options;
    }
}