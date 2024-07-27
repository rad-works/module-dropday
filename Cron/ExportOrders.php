<?php
declare(strict_types=1);

namespace DmiRud\Dropday\Cron;

use Psr\Log\LoggerInterface;
use DmiRud\Dropday\Model\Config;
use DmiRud\Dropday\Model\Export\OrderService;

/**
 * Runs orders exporting to DropDay API
 */
class ExportOrders
{
    public function __construct(
        private readonly Config          $config,
        private readonly OrderService    $orderService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isCronEnabled()) {
            return;
        }

        foreach ($this->orderService->getList()->getItems() as $order) {
            try {
                $this->orderService->exportOrder($order);
            } catch (\Throwable $e) {
                $this->logger->error(
                    'DropDay Export Error (' . $order->getIncrementId() . '):' . $e->getMessage()
                );
            }
        }
    }
}
