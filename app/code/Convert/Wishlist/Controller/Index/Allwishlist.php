<?php

namespace Convert\Wishlist\Controller\Index;

use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;

/**
 * Class Allwishlist
 *
 * @package Convert\Wishlist\Controller\Index
 */
class Allwishlist extends \Magento\Wishlist\Controller\AbstractIndex
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

    /**
     * @var CheckoutCart
     */
    protected $cart;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * Allwishlist constructor.
     *
     * @param Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param WishlistHelper $wishlistHelper
     * @param CheckoutCart $cart
     * @param CartHelper $cartHelper
     * @param Escaper $escaper
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Action\Context $context,
        WishlistProviderInterface $wishlistProvider,
        WishlistHelper $wishlistHelper,
        CheckoutCart $cart,
        CartHelper $cartHelper,
        Escaper $escaper,
        Validator $formKeyValidator
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->wishlistHelper = $wishlistHelper;
        $this->cart = $cart;
        $this->cartHelper = $cartHelper;
        $this->escaper = $escaper;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Add cart item to wishlist and remove from cart
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }

        try {
            $quote = $this->cart->getQuote();
            foreach($quote->getAllVisibleItems() as $item){
                $productId = $item->getProductId();
                $buyRequest = $item->getBuyRequest();
                $wishlist->addNewItem($productId, $buyRequest);
                $wishlist->save();
                $this->cart->getQuote()->removeItem($item->getId());
            }
            $this->cart->save();

            $this->wishlistHelper->calculate();

            $this->messageManager->addSuccessMessage(__(
                "The products has been moved to your wish list."
            ));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t move the item to the wish list.'));
        }
        return $resultRedirect->setUrl($this->wishlistHelper->getListUrl($wishlist));
    }
}
