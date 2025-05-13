<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Block\Adminhtml\Form\Renderer\Config\Order;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Sales\Model\Order\Config as OrderConfig;
use RadWorks\Dropday\Model\Config;

/**
 * Provides list of all order statuses grouped by state
 */
class Status extends Select
{
    /**
     * Optional field name in the form
     */
    public const FIELD_NAME = Config::FIELD_NAME_STATUS;

    /**
     * @var OrderConfig
     */
    private OrderConfig $orderConfig;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param OrderConfig $orderConfig
     * @param array $data
     */
    public function __construct(Context $context, OrderConfig $orderConfig, array $data = [])
    {
        parent::__construct($context, $data);
        $this->orderConfig = $orderConfig;
    }

    /**
     * Render HTML
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $options = [];
            foreach ($this->orderConfig->getStates() as $state => $stateLabel) {
                $value = [];
                foreach ($this->orderConfig->getStateStatuses($state) as $status => $label) {
                    $value[$state . '_' . $status] = $label;
                }

                $options[] = ['label' => $stateLabel, 'value' => $value];
            }

            $this->setOptions($options);
        }

        $this->setExtraParams('multiple="multiple" style="min-width: 200px;min-height:200px;"');

        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value): static
    {
        return $this->setName($value . '[]');
    }
}