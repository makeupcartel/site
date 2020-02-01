<?php

namespace Convert\SwarmingSubscribePro\Observer\Frontend\CheckoutCart;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Swarming\SubscribePro\Model\Quote\SubscriptionOption\OptionProcessor as SubscriptionOptionProcessor;
use SubscribePro\Exception\InvalidArgumentException;
use SubscribePro\Exception\HttpException;
use Swarming\SubscribePro\Api\Data\SubscriptionOptionInterface;
use SubscribePro\Service\Product\ProductInterface as PlatformProductInterface;

class CartUpdateItemsBefore extends \Swarming\SubscribePro\Observer\CheckoutCart\CheckoutCartAbstract implements ObserverInterface
{
    /**
     * @var array
     */
    protected $warnings = [];

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->generalConfig->isEnabled()) {
            return;
        }

        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $observer->getData('cart');
        $quote = $cart->getQuote();
        $isSubscription = false;
        /** @var \Magento\Framework\DataObject $infoDataObject */
        $infoDataObject = $observer->getData('info');
        $cartData = $infoDataObject->getData();
        $cartExist = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $info = 'product-'.$item->getProduct()->getId();
            if ($option = $item->getOptionByCode('simple_product')) {
                $productId = $option->getProduct()->getId();
                $info .= '-simple_product-'.$productId;
            }
            if(isset($cartData[$item->getId()]['subscription_option'])){
                $isSubscription = true;
                $info .= '-subs-option-'.$cartData[$item->getId()]['subscription_option']['option'];
                $info .= '-subs-interval-'.$cartData[$item->getId()]['subscription_option']['interval'];
            }
            if($isSubscription){
                if(in_array($info, $cartExist)){
                    $k = array_search($info, $cartExist);
                    $cartData[$k]['qty'] = $cartData[$k]['qty'] + $cartData[$item->getId()]['qty'];
                    $cartData[$k]['item'] = $item;
                    unset($cartData[$item->getId()]);
                }else{
                    $cartExist[$item->getId()] = $info;
                }
            }
        }
        if($isSubscription && count($cartData) < count($quote->getAllVisibleItems())){
            $this->updateCart($cartData);
        }
    }

    /**
     * @param array $cartData
     */
    protected function updateCart(array $cartData)
    {
        foreach($cartData as $cData){
            $quoteItem = $cData['item'];
            $product = $quoteItem->getProduct();
            if (!$this->productHelper->isSubscriptionEnabled($product)) {
                return;
            }

            $platformProduct = $this->getPlatformProduct($product);
            if (!$platformProduct) {
                return;
            }
            $subscriptionOption = $this->getSubscriptionOption(
                                    $platformProduct,
                                    $this->getParamData($cData['subscription_option'], SubscriptionOptionInterface::OPTION)
                                );
            if ($subscriptionOption == PlatformProductInterface::SO_SUBSCRIPTION) {
                $this->validateQuantity($cData['qty'], $platformProduct, $product);
                $this->validateInterval($platformProduct, $this->getParamData($cData['subscription_option'], SubscriptionOptionInterface::INTERVAL));
            }
            foreach ($this->getWarnings() as $message) {
                $this->messageManager->addWarningMessage($message);
                throw new \Magento\Framework\Exception\LocalizedException(__($message));
            }
        }
    }

    /**
     * @param \SubscribePro\Service\Product\ProductInterface $platformProduct
     * @param string|null $subscriptionOption
     * @return string
     */
    protected function getSubscriptionOption(PlatformProductInterface $platformProduct, $subscriptionOption = null)
    {
        if ($platformProduct->getSubscriptionOptionMode() == PlatformProductInterface::SOM_SUBSCRIPTION_ONLY) {
            $subscriptionOption = PlatformProductInterface::SO_SUBSCRIPTION;
        } else if (!$this->validateIntervals($platformProduct, true)) {
            $subscriptionOption = PlatformProductInterface::SO_ONETIME_PURCHASE;
        }
        return $subscriptionOption ?: $platformProduct->getDefaultSubscriptionOption();
    }

    /**
     * @param \SubscribePro\Service\Product\ProductInterface $platformProduct
     * @param bool $graceful
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateIntervals($platformProduct, $graceful = false)
    {
        if (!empty($platformProduct->getIntervals())) {
            return true;
        } else if ($graceful) {
            return false;
        }
        throw new LocalizedException(__('The product is not configured properly, please contact customer support.'));
    }

    /**
     * @param \Swarming\SubscribePro\Api\Data\ProductInterface $platformProduct
     * @param string|null $subscriptionInterval
     * @return null|string
     * @throws \Exception
     */
    protected function validateInterval($platformProduct, $subscriptionInterval)
    {
        if (!empty($subscriptionInterval) && !in_array($subscriptionInterval, $platformProduct->getIntervals())) {
            $this->warnings[] = __('Subscription interval is not valid.');
            return false;
        }
        return true;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param \Swarming\SubscribePro\Api\Data\ProductInterface $platformProduct
     */
    protected function validateQuantity($qty, $platformProduct, $product)
    {
        if (($platformProduct->getMinQty() && $qty < $platformProduct->getMinQty())) {
            $this->warnings[] = __(
                'Product "%1" requires minimum quantity of %2 for subscription.',
                $product->getName(),
                $platformProduct->getMinQty()
            );
        } else if ($platformProduct->getMaxQty() && $qty > $platformProduct->getMaxQty()) {
            $this->warnings[] = __(
                'Product "%1" allows maximum quantity of %2 for subscription.',
                $product->getName(),
                $platformProduct->getMaxQty()
            );
        }
    }

    /**
     * @param array $params
     * @param string $key
     * @return string|null
     */
    protected function getParamData(array $params, $key)
    {
        return isset($params[$key]) ? $params[$key] : null;
    }

    /**
     * @return array
     */
    protected function getWarnings()
    {
        $warnings = $this->warnings;
        $this->warnings = [];
        return $warnings;
    }
}
