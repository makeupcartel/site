<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Block;

use Magento\Framework\View\Element\Template;

class Notification extends \Magento\Framework\View\Element\Template
{
    const VAR_ENABLED = 'messages/display_notification';
    const VAR_TEXT = 'messages/notification_text';
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'notification.phtml';

    /**
     * @var \Amasty\Promo\Helper\Config
     */
    protected $config;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    protected $promoHelper;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Amasty\Promo\Helper\Config $config,
        \Amasty\Promo\Helper\Data $promoHelper,
        array $data = []
    ) {
        $this->config = $config;
        $this->promoHelper = $promoHelper;
        $this->_isScopePrivate = true;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (int)$this->config->getScopeValue(self::VAR_ENABLED) === 1;
    }

    /**
     * @return int
     */
    public function getNewItemsCount()
    {
        $count = 0;
        if (($items = $this->promoHelper->getNewItems()) &&
            $items instanceof \Magento\Catalog\Model\ResourceModel\Product\Collection){
            $count = $items->getSize();
        }
        return $count;
    }

    /**
     * @return int
     */
    public function getText()
    {
        $placeholders = [
            '{url checkout/cart}' => $this->getUrl('checkout/cart')
        ];

        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->config->getScopeValue(self::VAR_TEXT)
        );
    }
}