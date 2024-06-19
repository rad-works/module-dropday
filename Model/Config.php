<?php
declare(strict_types=1);

namespace DmiRud\Dropday\Model;

use Dropday\OrderAutomation\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * The module's configuration model.
 */
class Config
{
    /**
     * Path or of the config value
     */
    private const XML_PATH_EXPORT_DELAY = 'DmiRud_dropday/order_export/delay';
    private const XML_PATH_EXPORT_ENABLED = 'DmiRud_dropday/order_export/enabled';
    private const XML_PATH_EXPORT_RULES = 'DmiRud_dropday/order_export/rules';

    /**
     * Order table  additional fields
     */
    public const ORDER_FIELD_EXPORT_STATUS = 'dropday_order_export_status';
    public const ORDER_FIELD_EXPORT_CREATED_AT = 'dropday_order_export_created_at';
    public const ORDER_FIELD_EXPORT_REFERENCE = 'dropday_order_id';
    public const ORDER_FIELD_EXPORT_STATUS_INITIAL = 0;
    public const ORDER_FIELD_EXPORT_STATUS_PENDING = 1;
    public const ORDER_FIELD_EXPORT_STATUS_COMPLETE = 2;
    public const ORDER_FIELD_EXPORT_STATUS_FAILED = 3;

    /**
     * Optional field name in the form
     */
    public const FIELD_NAME_PAYMENT = 'payment_method';
    public const FIELD_NAME_STATUS = 'order_status';

    public function __construct(
        private readonly Data                 $dropDayHelper,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SerializerInterface  $serializer
    )
    {
    }

    /**
     * Check if order export enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return !!$this->dropDayHelper->isEnabled();
    }

    /**
     * Check if cron order export enabled
     *
     * @return bool
     */
    public function isCronEnabled(): bool
    {
        return $this->isEnabled() && $this->scopeConfig->isSetFlag(self::XML_PATH_EXPORT_ENABLED);
    }

    /**
     * Get order export delay in minutes
     *
     * @return int
     */
    public function getExportDelayInMinutes(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_EXPORT_DELAY);
    }

    /**
     * Get states that allows order to be exported.
     *
     * @return int[]
     */
    public function getAllowedExportStates(): array
    {
        return [self::ORDER_FIELD_EXPORT_STATUS_FAILED, self::ORDER_FIELD_EXPORT_STATUS_PENDING];
    }

    /**
     * Get export rules based on payment method and order status
     *
     * @return array
     */
    public function getExportRules(): array
    {
        $rules = [];
        try {
            $rows = $this->serializer->unserialize($this->scopeConfig->getValue(self::XML_PATH_EXPORT_RULES));
            foreach ($rows ?: [] as $row) {
                [self::FIELD_NAME_PAYMENT => $index, self::FIELD_NAME_STATUS => $value] = $row;
                $rules[$index] = $value;
            }
        } catch (\InvalidArgumentException) {
            return $rules;
        }

        return $rules;
    }
}