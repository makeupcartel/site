<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Controller\Cart;

use Amasty\Promo\Helper\Cart;
use Amasty\Promo\Model\ItemRegistry\PromoItemRegistry;
use Amasty\Promo\Model\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\QuoteRepository;

/**
 * Add Promo Items action
 */
class Add extends \Magento\Framework\App\Action\Action
{
    const KEY_QTY_ITEM_PREFIX = 'ampromo_qty_select_';

    /**
     * @var Registry
     */
    protected $promoRegistry;

    /**
     * @var Cart
     */
    protected $promoCartHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PromoItemRegistry
     */
    private $promoItemRegistry;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * Request whitelist parameters
     * @var array
     */
    private $requestOptions = [
        'super_attribute',
        'options',
        'super_attribute',
        'links',
        'giftcard_sender_name',
        'giftcard_sender_email',
        'giftcard_recipient_name',
        'giftcard_recipient_email',
        'giftcard_message',
        'giftcard_amount',
        'custom_giftcard_amount'
    ];

    public function __construct(
        Context $context,
        Registry $promoRegistry,
        Cart $promoCartHelper,
        ProductRepositoryInterface $productRepository,
        PromoItemRegistry $promoItemRegistry,
        Session $checkoutSession,
        QuoteRepository $quoteRepository
    ) {
        parent::__construct($context);
        $this->promoRegistry = $promoRegistry;
        $this->promoCartHelper = $promoCartHelper;
        $this->productRepository = $productRepository;
        $this->promoItemRegistry = $promoItemRegistry;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getItemsRequest();

        $updateTotalQty = false;

        $quote = $this->checkoutSession->getQuote();

        foreach ($data as $params) {
            if (empty($params)) {
                continue;
            }

            $productId = (int)$params['product_id'];

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId);
            $sku = $product->getSku();
            /** @var \Amasty\Promo\Model\ItemRegistry\PromoItemData $promoDataItem */
            $promoDataItem = $this->getPromoDataItem($sku, $params);
            if ($promoDataItem) {
                $qty = $this->getQtyToAdd($promoDataItem, $params, $productId);
                $updateTotalQty = true;
                $requestOptions = array_intersect_key($params, array_flip($this->requestOptions));

                $this->promoCartHelper->addProduct(
                    $product,
                    $qty,
                    $promoDataItem,
                    $requestOptions,
                    $quote
                );
            }
        }

        if ($updateTotalQty) {
            $this->quoteRepository->save($quote);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();

        return $resultRedirect;
    }

    /**
     * @return array
     */
    protected function getItemsRequest()
    {
        if (!($data = $this->getRequest()->getParam('data', false))) {
            $data[] = $this->getRequest()->getParams();
        }

        return $data;
    }

    /**
     * @param string $sku
     * @param array $params
     *
     * @return \Amasty\Promo\Model\ItemRegistry\PromoItemData|null
     * @since 2.5.0 promo item data is filtering by rule_id and sku instead only by sku
     */
    protected function getPromoDataItem($sku, $params)
    {
        if (isset($params['rule_id']) && $ruleId = (int)$params['rule_id']) {
            $promoItemData = $this->promoItemRegistry->getItemBySkuAndRuleId($sku, $ruleId);
            if ($promoItemData && $promoItemData->getQtyToProcess() > 0) {
                return $promoItemData;
            }
        } else {
            $promoItemsData = $this->promoItemRegistry->getItemsBySku($sku);
            foreach ($promoItemsData as $promoItemData) {
                if ($promoItemData->getQtyToProcess() > 0) {
                    return $promoItemData;
                }
            }
        }

        return null;
    }

    /**
     * @param \Amasty\Promo\Model\ItemRegistry\PromoItemData $promoDataItem
     * @param array $params
     * @param int $productId
     *
     * @return float
     */
    protected function getQtyToAdd($promoDataItem, $params, $productId)
    {
        $qty = $promoDataItem->getQtyToProcess();
        if (isset($params[self::KEY_QTY_ITEM_PREFIX . $productId])
            && $params[self::KEY_QTY_ITEM_PREFIX . $productId] <= $qty
        ) {
            $qty = $params[self::KEY_QTY_ITEM_PREFIX . $productId];
        }

        return (float)$qty;
    }
}
