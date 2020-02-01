<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Customers\Abandoned;

use Magento\Backend\Block\Template;

/**
 * Class Information
 */
class Information extends Template
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        $html = '';
        if (!$this->isAcartEnabled() || !$this->isRequestQuoteEnabled()) {
            $html = parent::toHtml();
        }
        return $html;
    }

    /**
     * @return bool
     */
    public function isAcartEnabled()
    {
        return $this->moduleManager->isEnabled('Amasty_Acart');
    }

    /**
     * @return bool
     */
    public function isRequestQuoteEnabled()
    {
        return $this->moduleManager->isEnabled('Amasty_RequestQuote');
    }
}
