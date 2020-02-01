<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Controller\Popup;

use Magento\Framework\App\Action\Action;

class Reload extends Action
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Layout $layout,
        \Amasty\Promo\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->layout = $layout;
        $this->helper = $helper;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $jsonResult */
        $jsonResult = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        $returnUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);

        if (!$returnUrl) {
            $jsonResult->setHttpResponseCode(403);
            return $jsonResult;
        }

        $products = $this->helper->getPromoItemsDataArray();
        $rawContent = '';
        if ($products['common_qty']) {
            $this->layout->getUpdate()->addHandle('amasty_promo_popup_reload');
            /** @var \Amasty\Promo\Block\Items $popupBlock */
            $popupBlock = $this->layout->getBlock('ampromo.items');
            $popupBlock->setData('current_url', $returnUrl);

            $rawContent = $popupBlock->toHtml();
        }

        $jsonResult->setData(['popup' => $rawContent, 'products' => $products], true);

        return $jsonResult;
    }
}
