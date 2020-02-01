<?php

namespace Convert\SwarmingSubscribePro\Observer\Frontend\CheckoutCart;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use SubscribePro\Exception\InvalidArgumentException;
use SubscribePro\Exception\HttpException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Swarming\SubscribePro\Api\Data\SubscriptionOptionInterface;

class AddProductToCartAfter extends \Swarming\SubscribePro\Observer\CheckoutCart\AddProductToCartAfter
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Swarming\SubscribePro\Model\Config\General $generalConfig
     * @param \Swarming\SubscribePro\Platform\Manager\Product $platformProductManager
     * @param \Swarming\SubscribePro\Model\Quote\SubscriptionOption\Updater $subscriptionOptionUpdater
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Swarming\SubscribePro\Helper\Product $productHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\State $appState
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Swarming\SubscribePro\Helper\QuoteItem $quoteItemHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Swarming\SubscribePro\Model\Config\General $generalConfig,
        \Swarming\SubscribePro\Platform\Manager\Product $platformProductManager,
        \Swarming\SubscribePro\Model\Quote\SubscriptionOption\Updater $subscriptionOptionUpdater,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Swarming\SubscribePro\Helper\Product $productHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\State $appState,
        \Psr\Log\LoggerInterface $logger,
        \Swarming\SubscribePro\Helper\QuoteItem $quoteItemHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $generalConfig,
            $platformProductManager,
            $subscriptionOptionUpdater,
            $productRepository,
            $productHelper,
            $messageManager,
            $appState,
            $logger,
            $quoteItemHelper
        );
    }

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

        /** @var array $items */
        $items = $observer->getData('items');

        foreach ($items as $quoteItem) {
            $subscriptionParams = $this->quoteItemHelper->getSubscriptionParams($quoteItem);
            $this->updateQuoteItem($quoteItem, $subscriptionParams);
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param array $quoteItemParams
     */
    protected function updateQuoteItem(QuoteItem $quoteItem, array $quoteItemParams)
    {
        $product = $quoteItem->getProduct();
        if ($quoteItem->getParentItem() && $quoteItem->getParentItem()->getProduct()) {
            $product = $quoteItem->getParentItem()->getProduct();
        }

        if (!$this->productHelper->isSubscriptionEnabled($product)) {
            return;
        }

        $platformProduct = $this->getPlatformProduct($product);
        if (!$platformProduct) {
            return;
        }

        $warnings = $this->subscriptionOptionUpdater->update(
            $quoteItem,
            $platformProduct,
            $this->getParamData($quoteItemParams, SubscriptionOptionInterface::OPTION),
            $this->getParamData($quoteItemParams, SubscriptionOptionInterface::INTERVAL)
        );

        foreach ($warnings as $message) {
            $this->messageManager->addErrorMessage($message);
            $this->checkoutSession->setSubscriptionProduct(true);
            throw new \Magento\Framework\Exception\LocalizedException(__($message));
        }
    }
}
