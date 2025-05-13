<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\FilterFactory;
use RadWorks\Dropday\Model\Config;
use RadWorks\Dropday\Model\Export\OrderService;

/**
 * Exports order by calling Dropday API
 */
class DropdayExport extends Action implements HttpPostActionInterface
{
    private CollectionFactory $collectionFactory;
    private FilterFactory $filterFactory;
    private OrderService $orderService;
    private Config $config;

    public function __construct(
        Config                   $config,
        CollectionFactory        $collectionFactory,
        FilterFactory            $filterFactory,
        OrderService             $orderService,
        Context                  $context
    )
    {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->filterFactory = $filterFactory;
        $this->orderService = $orderService;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {

            if (!$this->config->isEnabled()) {
                throw new LocalizedException(__('Dropday Export is disabled.'));
            }

            $collection = $this->filterFactory->create()->getCollection($this->collectionFactory->create());
            /** @var OrderInterface $order */
            foreach ($collection->getItems() as $order) {
                $incrementId = $order->getIncrementId();
                $exportStatus = $order->getData(Config::ORDER_FIELD_EXPORT_STATUS);
                if ($exportStatus == Config::ORDER_FIELD_EXPORT_STATUS_COMPLETE) {
                    $this->messageManager->addNoticeMessage(__('Skipped. Order #%1 has already been exported.', $incrementId));
                    continue;
                }

                if ($this->orderService->exportOrder($order)) {
                    $this->messageManager->addSuccessMessage(__('Order #%1 has been exported.', $incrementId));
                } else {
                    $this->messageManager->addErrorMessage(__('Error exporting order #%1.', $incrementId));
                }
            }
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}