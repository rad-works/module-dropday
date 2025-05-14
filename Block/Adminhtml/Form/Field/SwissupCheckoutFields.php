<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;
use RadWorks\Dropday\Block\Adminhtml\Form\Renderer\Config\Order\SwissupCheckoutField as CheckoutFields;
use RadWorks\Dropday\Model\Config;

class SwissupCheckoutFields extends AbstractFieldArray
{
    /**
     * @var AbstractBlock[]
     */
    protected array $renderer = [];

    /**
     * Prepare to render
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn(Config::FIELD_NAME_ADDITIONAL_VARIABLE_NAME, [
            'label' => __('Export Variable Name'),
            'class' => 'required-entry'
        ]);
        $this->addColumn(CheckoutFields::FIELD_NAME,
            [
                'label' => __('Swissup Checkout Field'),
                'renderer' => $this->getRenderer(CheckoutFields::class)
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Export Field');
    }

    /**
     * Provides renderer block
     *
     * @param string $class
     * @return AbstractBlock
     * @throws LocalizedException
     */
    protected function getRenderer(string $class): AbstractBlock
    {
        if (!array_key_exists($class, $this->renderer)) {
            $this->renderer[$class] = $this->getLayout()->createBlock(
                $class,
                '',
                ['data' => ['value' => $this->getValue(), 'is_render_to_js_template' => true]]
            );
        }

        return $this->renderer[$class];
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        if ($fieldValue = $row->getData(CheckoutFields::FIELD_NAME)) {
            $options['option_' . $this->getRenderer(CheckoutFields::class)->calcOptionHash($fieldValue)]
                = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}