<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Controller\Cart;

class UpdateItemOptions extends Add
{
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();
        $resultStatus = 0;
        $this->setProduct($this->_initProduct());

        if (!isset($params['options'])) {
            $params['options'] = [];
        }
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->localeResolver->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $this->cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                $message = __('We can\'t find the quote item.');
                return $this->addToCartResponse($message, 0);
            }

            $item = $this->cart->updateItem($id, new \Magento\Framework\DataObject($params));
            if (is_string($item)) {
                $message = __($item);
                return $this->addToCartResponse($message, 0);
            }

            if ($item->getHasError()) {
                $message = __($item->getMessage());
                return $this->addToCartResponse($message, 0);
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            $this->_eventManager->dispatch(
                'checkout_cart_update_item_complete',
                ['item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->cart->getQuote()->getHasError()) {
                $message = __(
                    '%1 was updated in your shopping cart.',
                    $this->escaper->escapeHtml($item->getProduct()->getName())
                );

                $resultStatus = 1;
            } else {
                $message = '';
                $errors = $this->cart->getQuote()->getErrors();
                foreach ($errors as $error) {
                    $message .= $error->getText();
                }
            }
        } catch (\Exception $e) {
            $message = __('We can\'t update the item right now.');
            $message .= ' ' . $e->getMessage();
        }

        return $this->addToCartResponse($message, $resultStatus);
    }
}
