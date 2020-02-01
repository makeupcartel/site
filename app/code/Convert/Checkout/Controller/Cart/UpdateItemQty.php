<?php

declare(strict_types=1);

namespace Convert\Checkout\Controller\Cart;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateItemQty
 *
 * @package Convert\Checkout\Controller\Cart
 */
class UpdateItemQty extends \Magento\Checkout\Controller\Cart\UpdateItemQty
{
    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpdateItemQty constructor.
     *
     * @param Context $context
     * @param RequestQuantityProcessor $quantityProcessor
     * @param FormKeyValidator $formKeyValidator
     * @param CheckoutSession $checkoutSession
     * @param Json $json
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        RequestQuantityProcessor $quantityProcessor,
        FormKeyValidator $formKeyValidator,
        CheckoutSession $checkoutSession,
        Json $json,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        LoggerInterface $logger
    ) {
        $this->quantityProcessor = $quantityProcessor;
        $this->formKeyValidator = $formKeyValidator;
        $this->checkoutSession = $checkoutSession;
        $this->json = $json;
        $this->cart = $cart;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        parent::__construct(
            $context,
            $quantityProcessor,
            $formKeyValidator,
            $checkoutSession,
            $json,
            $logger
        );
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(
                    __('Something went wrong while saving the page. Please refresh the page and try again.')
                );
            }

            $cartData = $this->getRequest()->getParam('cart');
            if (!is_array($cartData)) {
                throw new LocalizedException(
                    __('Something went wrong while saving the page. Please refresh the page and try again.')
                );
            }

            $cartData = $this->quantityProcessor->process($cartData);
            $quote = $this->checkoutSession->getQuote();

            foreach ($cartData as $itemId => $itemInfo) {
                $item = $quote->getItemById($itemId);
                $qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : 0;
                if ($item) {
                    $this->updateItemQuantity($item, $qty);
                }
            }
            $this->cart->save();
            $this->jsonResponse();
        } catch (LocalizedException $e) {
            $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->jsonResponse('Something went wrong while saving the page. Please refresh the page and try again.');
        }
    }

    /**
     * Updates quote item quantity.
     *
     * @param Item $item
     * @param float $qty
     * @throws LocalizedException
     */
    private function updateItemQuantity(Item $item, float $qty)
    {
        if ($qty > 0) {
            $item->setQty($qty);

            if ($item->getHasError()) {
                throw new LocalizedException(__($item->getMessage()));
            }
        }
    }

    /**
     * JSON response builder.
     *
     * @param string $error
     * @return void
     */
    private function jsonResponse(string $error = '')
    {
        $this->getResponse()->representJson(
            $this->json->serialize($this->getResponseData($error))
        );
    }

    /**
     * Returns response data.
     *
     * @param string $error
     * @return array
     */
    private function getResponseData(string $error = ''): array
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $layout = $resultPage->addHandle('checkout_cart_index')->getLayout();

        $promoAddBlock = $layout->getBlock('ampromo.add');
        $response = [
            'success' => true,
            'cart' => $layout->getBlock('checkout.cart.form')->toHtml(),
            'sourceUrl' => $promoAddBlock->getUrl('amasty_promo/popup/reload'),
            'uenc' => $promoAddBlock->getCurrentBase64Url(),
            'commonQty' => $promoAddBlock->getAvailableProductQty()['common_qty'],
            'products' => \Zend_Json::encode($promoAddBlock->getAvailableProductQty()),
            'formUrl' => $promoAddBlock->getFormActionUrl(),
            'selectionMethod' => $promoAddBlock->getSelectionMethod(),
            'giftsCounter' => $promoAddBlock->getGiftsCounter(),
            'autoOpenPopup' => $promoAddBlock->isOpenAutomatically() ? 1 : 0
        ];

        if (!empty($error)) {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }

        return $response;
    }
}
