<?php
declare(strict_types=1);

namespace DmiRud\Dropday\Model;

use Dropday\OrderAutomation\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Dropday API client
 */
class DropdayAdapter
{
    public function __construct(
        private readonly Data                     $helper,
        private readonly Json                     $json,
        private readonly LoggerInterface          $logger,
        private readonly OrderRepositoryInterface $orderRepository
    )
    {
    }

    /**
     * Create an order
     *
     * @see https://dropday.io/api/v1/orders
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function createOrder(OrderInterface $order): bool
    {
        //This method is a copy of \Dropday\OrderAutomation\Observer\Sales\OrderPlaceAfter::execute
        try {
            // Additional checks for account ID and API key
            if (!$this->helper->getAccountId() || !$this->helper->getApiKey()) {
                $this->logger->warning('Dropday Automation: Missing Account ID or API Key in system configuration!');
                return false;
            }

            $client = $this->helper->getClient();
            $postData = $this->helper->getOrderRequestData($order);
            // POST request to the API
            $client->post($this->helper->getBaseUrl() . '/orders', json_encode($postData));
            // Logging for test mode
            if ($this->helper->isTestMode()) {
                $this->logger->info('Dropday Request: ' . json_encode($postData));
            }

            // Handling the response
            $responseBody = $client->getBody();
            $response = $this->json->unserialize($responseBody);
            $statusCode = $client->getStatus();
            if ($statusCode == 200 && isset($response['reference'])) {
                // Update order data and save
                $order->setData(Config::ORDER_FIELD_EXPORT_REFERENCE, $response['reference']);
                // Add order comment
                $order->addCommentToStatusHistory('Dropday order-ID: ' . $response['reference']);
                $this->orderRepository->save($order);
            } else {
                // Add error comment to order
                $order->addCommentToStatusHistory('Dropday API Error: (' . $statusCode . ') ' . json_encode($response));
                $this->orderRepository->save($order);
                return false;
            }
        } catch (\Exception $e) {
            // Log critical errors
            $this->logger->critical('Dropday Automation: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}