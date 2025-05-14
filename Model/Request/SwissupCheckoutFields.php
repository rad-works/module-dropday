<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Model\Request;

use Magento\Framework\Escaper;
use Magento\Sales\Api\Data\OrderInterface;
use RadWorks\Dropday\Model\Config;
use Swissup\CheckoutFields\Helper\Data;

class SwissupCheckoutFields implements OrderAdditionalDataInterface
{
    /**
     * XML config path
     */
    private const XML_PATH_CHECKOUT_FIELDS = 'radworks_dropday/order_export/swissup_checkout_fields';

    /**
     * @var Escaper $escaper
     */
    private Escaper $escaper;

    /**
     * @var Data $checkoutFieldsHelper
     */
    private Data $checkoutFieldsHelper;

    /**
     * @var Config $config
     */
    private Config $config;

    /**
     * @param Escaper $escaper
     * @param Config $config
     * @param Data $checkoutFieldsHelper
     */
    public function __construct(Escaper $escaper, Config $config, Data $checkoutFieldsHelper)
    {
        $this->escaper = $escaper;
        $this->config = $config;
        $this->checkoutFieldsHelper = $checkoutFieldsHelper;
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function getParams(OrderInterface $order): array
    {
        $params = [];
        if (!$this->checkoutFieldsHelper->isEnabled()) {
            return [];
        }

        $variables = $this->getExportAdditionVariables();
        $fields = $this->checkoutFieldsHelper->getOrderFieldsValues($order, array_keys($variables));
        foreach ($fields as $field) {
            if (array_key_exists($field->getCode(), $variables)) {
                $params[$variables[$field->getCode()]] = $this->escaper->escapeHtml($field->getValue());
            }
        }

        return $params;
    }

    /**
     * @return array
     */
    private function getExportAdditionVariables(): array
    {
        $result = [];
        foreach ($this->config->getArrayValue(self::XML_PATH_CHECKOUT_FIELDS) as $value) {
            $result[$value[Config::FIELD_NAME_SWISSUP_CHECKOUT_FIELD]] = $value[Config::FIELD_NAME_ADDITIONAL_VARIABLE_NAME];
        };


        return $result;
    }
}