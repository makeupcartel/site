<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Block;

use Magento\Framework\View\Element\Template;

/**
 * Popup Style
 */
class Popup extends \Magento\Framework\View\Element\Template
{
    const POPUP_ONE_BY_ONE = 0;
    const POPUP_MULTIPLE = 1;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $modelConfig;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $promoHelper;

    public function __construct(
        Template\Context $context,
        \Amasty\Promo\Model\Config $modelConfig,
        \Amasty\Promo\Helper\Data $promoHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->modelConfig = $modelConfig;
        $this->promoHelper = $promoHelper;
    }

    /**
     * @return int|null
     */
    public function getCountersMode()
    {
        return $this->modelConfig->getScopeValue("messages/display_remaining_gifts_counter");
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getPopupName()
    {
        $popupTitle = $this->modelConfig->getPopupName();

        if (!$popupTitle) {
            $popupTitle = __('Free Items');
        }

        return $popupTitle;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        $newItems = $this->promoHelper->getNewItems();

        return $newItems ? $newItems->getSize() : 0;
    }
}
