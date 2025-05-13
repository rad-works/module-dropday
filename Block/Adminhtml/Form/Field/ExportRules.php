<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\AbstractBlock;
use RadWorks\Dropday\Block\Adminhtml\Form\Renderer\Config\Order\PaymentMethod;
use RadWorks\Dropday\Block\Adminhtml\Form\Renderer\Config\Order\Status;

class ExportRules extends AbstractFieldArray
{
    /**
     * @var AbstractBlock[]
     */
    protected array $renderer = [];

    /**
     * Provides renderer block
     *
     * @param string $class
     * @return AbstractBlock
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
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn(
            PaymentMethod::FIELD_NAME,
            [
                'label' => __('Payment Method'),
                'renderer' => $this->getRenderer(PaymentMethod::class)
            ]
        );
        $this->addColumn(
            Status::FIELD_NAME,
            [
                'label' => __('Order Status'),
                'renderer' => $this->getRenderer(Status::class)
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Rule');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        if ($paymentValue = $row->getData(PaymentMethod::FIELD_NAME)) {
            $options['option_' . $this->getRenderer(PaymentMethod::class)->calcOptionHash($paymentValue)]
                = 'selected="selected"';
            foreach ((array) $row->getData(Status::FIELD_NAME) ?:[]  as $status) {
                $options['option_' . $this->getRenderer(Status::class)->calcOptionHash($status)]
                    = 'selected="selected"';
            }
        }

        $row->setData('option_extra_attrs', $options);
    }
}