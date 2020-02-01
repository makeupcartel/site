<?php


namespace Convert\Stamped\Rewrite\Stamped\Core\Helper;

use Stamped\Core\Helper\Data as StampedData;

class Data extends StampedData
{
    public function saveOrderAfter($order)
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $store_id = $order->getStoreId();
            $orderStatuses = $this->getConfigValue('core/stamped_settings/order_status_trigger');
            if ($orderStatuses == null) {
                $orderStatuses = array('complete');
            } else {
                $orderStatuses = array_map('strtolower', explode(',', $orderStatuses));
            }
            if (!$this->isConfigured($store_id)) {
                return $this;
            }

            if (!in_array($order->getStatus(), $orderStatuses)) {
                return $this;
            }
            $data = array();
            if (!$order->getCustomerIsGuest()) {
                $data["user_reference"] = $order->getCustomerId();
            }
            // convert fix
            $address = $order->getBillingAddress();
            if (!$order->getIsVirtual() && $order->getShippigAddressId()>0) {
                $address = $order->getShippingAddress();
            }
            $shippingId = $address->getId();
            $address = $objectManager->create('Magento\Customer\Model\Address')->load($shippingId);

            $data = array();
            if (!$order->getCustomerIsGuest()) {
                $data["userReference"] = $order->getCustomerEmail();
            }

            $data["customerId"] = $order->getCustomerId();
            $data["email"] = $order->getCustomerEmail();
            $data["firstName"] = $order->getCustomerFirstname();
            $data["lastName"] = $order->getCustomerLastname();
            $data["location"] = $address->getCountry();
            $data['orderNumber'] = $order->getIncrementId();
            $data['orderId'] = $order->getIncrementId();
            $data['orderCurrencyISO'] = $order->getOrderCurrency()->getCode();
            $data["orderTotalPrice"] = $order->getGrandTotal();
            $data["orderSource"] = 'magento';
            if($order->getCreatedAt()){
                $data["orderDate"] = $order->getCreatedAt();
            }else{
                $data["orderData"] = date('Y-m-d H:m:s');
            }
            $data['itemsList'] = $this->getOrderProductsData($order);
            $data['platform'] = 'magento';

            $this->createReviewRequest($data, $store_id);

            return $this;

        } catch(Exception $e) {
            return;
        }
        return $this;
    }
}
