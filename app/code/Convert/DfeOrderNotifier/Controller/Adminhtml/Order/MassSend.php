<?php

namespace Convert\DfeOrderNotifier\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Dfe\OrderNotifier\Payload;
use Magento\Framework\App\Config;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;

/**
 * Class MassSend
 *
 * @package Convert\DfeOrderNotifier\Controller\Adminhtml\Order
 */
class MassSend extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction implements HttpPostActionInterface
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
     * MassSend constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @param Curl $curl
     * @param Payload $payload
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Config $config,
        Curl $curl,
        Payload $payload
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->_config = $config;
        $this->_curl = $curl;
        $this->_payload = $payload;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        $count = 0;
        foreach ($collection->getItems() as $order) {
            if ($this->_config->getValue('mage2pro/order_notifier/enable', ScopeInterface::SCOPE_STORE, $order->getStore())) {
                try {
                    $data = $this->_payload->get($order);
                    if (isset($data['customer_id'])) {
                        $data['customer_id'] = $data['customer']['entity_id'];
                    }
                    $res = $this->post($data, $order->getStore());
                } catch (\Exception $e) {
                    $res = $e->getMessage();
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
                $count++;
            }
        }
        $countNon = $collection->count() - $count;

        if ($countNon && $count) {
            $this->messageManager->addErrorMessage(__('%1 order(s) were not put on Netsuite.', $countNon));
        } elseif ($countNon) {
            $this->messageManager->addErrorMessage(__('No order(s) were put on Netsuite.'));
        }

        if ($count) {
            $this->messageManager->addSuccessMessage(__('You have put %1 order(s) on Netsuite.', $count));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
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
