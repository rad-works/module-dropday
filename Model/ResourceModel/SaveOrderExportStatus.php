<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderInterface;

class SaveOrderExportStatus
{
    /**
     * @param ResourceConnection $connection
     */
    public function __construct(private readonly ResourceConnection $connection) {
    }

    /**
     * @param int $orderId
     * @param array $data
     *
     * @return void
     */
    public function execute(int $orderId, array $data = []): void
    {
        $connection = $this->connection->getConnection('sales');
        $connection->update(
            $connection->getTableName('sales_order'),
            $data,
            [$connection->quoteIdentifier(OrderInterface::ENTITY_ID) . ' = ?' => $orderId]
        );
    }
}
