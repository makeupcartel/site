<?php

namespace Convert\DfeOrderNotifier\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Dfe\OrderNotifier\Payload;
use Magento\Framework\App\Config;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Send
 *
 * @package Convert\DfeOrderNotifier\Controller\Adminhtml\Order
 */
class Send extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var Curl
     */
    protected $_curl;

    /**
     * @var Payload
     */
    protected $_payload;

    /**
     * Send constructor.
     *
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param Config $config
     * @param Curl $curl
     * @param Payload $payload
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        Config $config,
        Curl $curl,
        Payload $payload,
        LoggerInterface $logger
    ) {
        $this->_config = $config;
        $this->_curl = $curl;
        $this->_payload = $payload;
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            if ($this->_config->getValue('mage2pro/order_notifier/enable', ScopeInterface::SCOPE_STORE, $order->getStore())) {
                try {
                    $data = $this->_payload->get($order);
                    if (isset($data['customer_id'])) {
                        $data['customer_id'] = $data['customer']['entity_id'];
                    }
                    $res = $this->post($data, $order->getStore());
                    $this->messageManager->addSuccessMessage(__('Your order sent to Netsuite.'));
                } catch (\Exception $e) {
                    $res = $e->getMessage();
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
                /** @var History|OrderStatusHistoryInterface $history */
                $history = $order->addStatusHistoryComment(__(
                    implode('<br>', [
                        "Manual order sent to Netsuite"
                        ,sprintf("The endpoint's response: «<b>%s</b>».", mb_substr($res, 0, 25000))
                    ])
                )); 

                $history->setIsVisibleOnFront(false);
                $history->setIsCustomerNotified(false);
                $history->save();
            }
            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                [
                    'order_id' => $order->getEntityId()
                ]
            );
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }

    /**
     * @param array $p
     * @param Store $s
     * @return string
     */
    protected function post(array $p, Store $s)
    {
        $url = $this->_config->getValue('mage2pro/order_notifier/url', ScopeInterface::SCOPE_STORE, $s);
        $debug = $this->_config->getValue('mage2pro/order_notifier/debug', ScopeInterface::SCOPE_STORE, $s);
        $this->_curl->addHeader('Content-Type', 'application/json');
        $this->_curl->addHeader('cache-control', 'no-cache');
        if (0 === strpos(strtolower($url), 'https') || false !== strpos($url, 'localhost')) {
            $this->_curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        }
        $this->_curl->post($url, json_encode($p, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        if ($debug) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/order-notify.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Order Data: ' . json_encode($p, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            $logger->info('Response: ' . $this->_curl->getBody());
        }
        return $this->_curl->getBody();
    }
}
