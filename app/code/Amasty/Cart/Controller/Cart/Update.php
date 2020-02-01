<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Controller\Cart;

use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Helper\Data as HelperData;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Exception\LocalizedException;

class Update extends Action
{
    /**
     * @var Sidebar
     */
    private $sidebar;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Amasty\Cart\Helper\Data
     */
    private $helper;

    public function __construct(
        Context $context,
        Sidebar $sidebar,
        Session $session,
        HelperData $helperData,
        ObjectFactory $objectFactory,
        \Amasty\Cart\Helper\Data $helper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->sidebar = $sidebar;
        $this->session = $session;
        $this->helperData = $helperData;
        $this->objectFactory = $objectFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->helper = $helper;
    }

    public function execute()
    {
        try {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(
                    __('We can\'t add this item to your shopping cart right now. Please reload the page.')
                );
            }

            $itemId = (int)$this->getRequest()->getParam('item_id');
            $itemQty = (int)$this->getRequest()->getParam('item_qty');

            if ($itemQty <= 0) {
                throw new LocalizedException(
                    __('We can\'t add this item to your shopping cart.')
                );
            }

            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->updateQuoteItem($itemId, $itemQty);
            $quote = $this->session->getQuote();

            $resultObject = $this->objectFactory->create(
                [
                    'data' => [
                        'result' => [
                            'items'    => $quote->getItemsSummaryQty() . __(' items'),
                            'subtotal' => $this->getSubtotalHtml()
                        ]
                    ]
                ]
            );
        } catch (LocalizedException $exception) {
            $resultObject = $this->objectFactory->create(
                [
                    'data' => [
                        'result' => [
                            'error' => $exception->getMessage()
                        ]
                    ]
                ]
            );
        } catch (\Exception $exception) {
            $resultObject = $this->objectFactory->create(
                [
                    'data' => [
                        'result' => [
                            'error' => __('We can\'t add this item to your shopping cart.')
                        ]
                    ]
                ]
            );
        }

        return $this->getResponse()->representJson(
            $this->helper->encode($resultObject->getResult())
        );
    }

    /**
     * @return string
     */
    protected function getSubtotalHtml()
    {
        $totals = $this->session->getQuote()->getTotals();
        $subtotal = isset($totals['subtotal']) && $totals['subtotal'] instanceof Total
            ? $totals['subtotal']->getValue()
            : 0;

        return $this->helperData->formatPrice($subtotal);
    }
}
