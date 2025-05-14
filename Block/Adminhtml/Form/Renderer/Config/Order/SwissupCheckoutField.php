<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Block\Adminhtml\Form\Renderer\Config\Order;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use RadWorks\Dropday\Model\Config;
use RadWorks\Dropday\Model\Config\Source\SwissupCheckoutFields as CheckoutFields;

class SwissupCheckoutField extends Select
{
    /**
     * Optional field name in the form
     */
    public const FIELD_NAME = Config::FIELD_NAME_SWISSUP_CHECKOUT_FIELD;

    /**
     * @var CheckoutFields $checkoutFields
     */
    private CheckoutFields $checkoutFields;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param CheckoutFields $checkoutFields
     * @param array $data
     */
    public function __construct(Context $context, CheckoutFields $checkoutFields, array $data = [])
    {
        parent::__construct($context, $data);
        $this->checkoutFields = $checkoutFields;
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
            $this->setOptions($this->checkoutFields->toOptionArray());
        }

        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     *
     * @return $this
     */
    public function setInputName($value): static
    {
        return $this->setName($value);
    }
}