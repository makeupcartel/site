<?php

namespace Convert\Wishlist\Controller\MultipleWishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Add
 *
 * @package Convert\Wishlist\Controller\MultipleWishlist
 */
class Add extends \Magento\MultipleWishlist\Controller\Index\Add
{
	public function execute()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

		/** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		if (!$this->formKeyValidator->validate($this->getRequest())) {
			return $resultRedirect->setPath('*/');
		}

		$wishlist = $this->wishlistProvider->getWishlist();
		if (!$wishlist) {
			throw new NotFoundException(__('Page not found.'));
		}

		$session = $this->_customerSession;

		$requestParams = $this->getRequest()->getParams();

		if ($session->getBeforeWishlistRequest()) {
			$requestParams = $session->getBeforeWishlistRequest();
			$session->unsBeforeWishlistRequest();
		}



		$productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;
		if (!$productId) {
			$resultRedirect->setPath('*/');
			return $resultRedirect;
		}

		try {
			$product = $this->productRepository->getById($productId);
		} catch (NoSuchEntityException $e) {
			$product = null;
		}

		if (!$product || !$product->isVisibleInCatalog()) {
			$this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
			$resultRedirect->setPath('*/');
			return $resultRedirect;
		}

		$resultRedirect1 = $storeManager->getStore()->getBaseUrl()."".$product->getUrl_key();

		try {
			$buyRequest = new \Magento\Framework\DataObject($requestParams);

			$result = $wishlist->addNewItem($product, $buyRequest);
			if (is_string($result)) {
				throw new \Magento\Framework\Exception\LocalizedException(__($result));
			}
			if ($wishlist->isObjectNew()) {
				$wishlist->save();
			}
			$this->_eventManager->dispatch(
				'wishlist_add_product',
				['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
			);

			$referer = $session->getBeforeWishlistUrl();
			if ($referer) {
				$session->setBeforeWishlistUrl(null);
			} else {
				$referer = $this->_redirect->getRefererUrl();
			}

			$this->_objectManager->get(\Magento\Wishlist\Helper\Data::class)->calculate();

			$this->messageManager->addComplexSuccessMessage(
				'addProductSuccessMessage',
				[
					'product_name' => $product->getName(),
					'referer' => $referer
				]
			);
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
			$this->messageManager->addErrorMessage(
				__('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
			);
		} catch (\Exception $e) {
			$this->messageManager->addExceptionMessage(
				$e,
				__('We can\'t add the item to Wish List right now.')
			);
		}

		$resultRedirect->setPath($resultRedirect1, ['wishlist_id' => $wishlist->getId()]);


		return $resultRedirect;
	}
}
