<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Block\Adminhtml\Form\Renderer\Config\Order;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Payment\Model\Config\Source\Allmethods;
use RadWorks\Dropday\Model\Config;

/**
 * Provides list of all available Payment methods
 */
class PaymentMethod extends Select
{
    /**
     * Optional field name in the form
     */
    public const FIELD_NAME = Config::FIELD_NAME_PAYMENT;

    /**
     * @var Allmethods
     */
    protected Allmethods $paymentMethods;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Allmethods $paymentMethods
     * @param array $data
     */
    public function __construct(Context $context, Allmethods $paymentMethods, array $data = [])
    {
        parent::__construct($context, $data);
        $this->paymentMethods = $paymentMethods;
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
            $this->setOptions($this->paymentMethods->toOptionArray());
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