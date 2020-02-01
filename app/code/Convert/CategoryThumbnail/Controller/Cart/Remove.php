<?php

namespace Convert\CategoryThumbnail\Controller\Cart;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\GiftCardAccount\Api\GiftCardAccountManagementInterface;

/**
 * Class Remove
 *
 * @package Convert\CategoryThumbnail\Controller\Cart
 */
class Remove extends \Magento\GiftCardAccount\Controller\Cart\Remove implements HttpGetActionInterface
{
    /**
     * @var GiftCardAccountManagementInterface
     */
    private $management;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Remove constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param Cart $cart
     * @param \Magento\Framework\Escaper $escaper
     * @param GiftCardAccountManagementInterface|null $management
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Escaper $escaper,
        ?GiftCardAccountManagementInterface $management = null
    ) {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart, $management);
        $this->management = $management
            ?? ObjectManager::getInstance()->get(GiftCardAccountManagementInterface::class);
        $this->escaper = $escaper;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');
        if ($code) {
            try {
                $this->management->deleteByQuoteId($this->cart->getQuote()->getId(), $code);
                $this->messageManager->addSuccessMessage(
                    __('Gift Card "%1" was removed.', $this->escaper->escapeHtml($code))
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Throwable $e) {
                $this->messageManager->addExceptionMessage($e, __('You can\'t remove this gift card.'));
            }
            return $this->_goBack();
        } else {
            $this->_forward('noroute');
        }
        return null;
    }
}
