<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Revert 'deleted' status and auto add all simple products without required options
 */
class AddressCollectTotalsAfterObserver implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    protected $promoRegistry;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    /**
     * @var \Amasty\Promo\Model\DiscountCalculator
     */
    private $discountCalculator;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Model\Config $config,
        \Amasty\Promo\Model\DiscountCalculator $discountCalculator
    ) {
        $this->_coreRegistry   = $registry;
        $this->_productFactory = $productFactory;
        $this->promoItemHelper = $promoItemHelper;
        $this->promoRegistry   = $promoRegistry;
        $this->config = $config;
        $this->discountCalculator = $discountCalculator;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();

        $items = $quote->getAllItems();

        $addAutomatically = $this->config->getAutoAddType();

        if ($addAutomatically) {
            $toAdd = $this->promoRegistry->getPromoItems();
            unset($toAdd['_groups']);

            foreach ($items as $item) {
                $sku = $item->getProduct()->getData('sku');

                foreach ($toAdd as &$rule) {
                    if (!isset($rule['sku'][$sku])) {
                        continue;
                    }

                    if ($this->discountCalculator->isEnableAutoAdd($rule['sku'][$sku]['discount'])) {
                        if ($this->promoItemHelper->isPromoItem($item)) {
                            $rule['sku'][$sku]['qty'] -= $item->getQty();
                        }
                    }
                }
            }

            $deleted = $this->promoRegistry->getDeletedItems();

            $this->_coreRegistry->unregister('ampromo_to_add');
            $collectorData = [];

            foreach ($toAdd as $ruleId => $ruleItem) {
                foreach ($ruleItem['sku'] as $sku => $item) {
                    if ($item['qty'] > 0 && $item['auto_add'] && !isset($deleted[$sku])) {
                        $product = $this->_productFactory->create()->loadByAttribute('sku', $sku);

                        if (isset($collectorData[$product->getId()])) {
                            $collectorData[$product->getId()]['qty'] += $item['qty'];
                        } else {
                            $collectorData[$product->getId()] = [
                                'product' => $product,
                                'discount' => $item['discount'],
                                'qty'     => $item['qty'],
                                'rule_id' => $ruleId
                            ];
                        }
                    }
                }
            }

            $this->_coreRegistry->register('ampromo_to_add', $collectorData);
        }
    }
}
