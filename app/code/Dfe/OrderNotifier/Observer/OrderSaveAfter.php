<?php

namespace Dfe\OrderNotifier\Observer;

use Dfe\OrderNotifier\Payload;
use Magento\Framework\App\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status\History;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Registry;

class OrderSaveAfter implements ObserverInterface 
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
	 * @var Registry 
	 */
	protected $_registry;

    /**
     * @var Payload
     */
	protected $_payload;

    /**
     * OrderSaveAfter constructor.
     *
     * @param Config $config
     * @param Curl $curl
     * @param Payload $payload
     * @param Registry $registry
     */
	public function __construct(
		Config $config,
		Curl $curl,
        Payload $payload,
		Registry $registry
	)
	{
		$this->_config = $config;
		$this->_curl = $curl;
		$this->_registry = $registry;
		$this->_payload = $payload;
	}

	/**
	 * @param Observer $observer
	 * @throws \Exception
	 */
	public function execute(Observer $observer)
	{
		$in = $this->_registry->registry('order_notify');
		if (!$in) {
			$order = $observer['order']; /** @var Order $order */
			try {
				if ($this->_config->getValue('mage2pro/order_notifier/enable', ScopeInterface::SCOPE_STORE, $order->getStore())) {
                    $this->_registry->unregister('order_notify');
                    $this->_registry->register('order_notify', $order->getIncrementId());
					list($state, $status) = [$order->getState(), $order->getStatus()];
					try {
					    $data = $this->_payload->get($order);
					    if (isset($data['customer_id'])) {
                            $data['customer_id'] = $data['customer']['entity_id'];
                        }
						$res = $this->post($data, $order->getStore());
					} catch (\Exception $e) {
						$res = $e->getMessage();
					}
					$history = $order->addStatusHistoryComment(__(
						implode('<br>', [
							"Order Notifier: the endpoint is notified."
							,"The order's status: «<b>{$status}</b>»."
							,"The order's state: «<b>{$state}</b>»."
							,sprintf("The endpoint's response: «<b>%s</b>».", mb_substr($res, 0, 25000))
						])
					)); /** @var History|OrderStatusHistoryInterface $history */
					$history->setIsVisibleOnFront(false);
					$history->setIsCustomerNotified(false);
					$history->save();
				}
			} finally {
			    $this->_registry->unregister('order_notify');
                $this->_registry->register('order_notify', $order->getIncrementId());
			}
		}
	}

	/**
	 * 2018-08-15
	 * @param array(string => mixed) $p
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