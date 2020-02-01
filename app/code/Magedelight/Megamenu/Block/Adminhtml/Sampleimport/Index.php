<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Megamenu\Block\Adminhtml\Sampleimport;

class Index extends \Magento\Framework\View\Element\Template
{

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->_storeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }
    
    /**
     * Generate form url
     *
     * @return  string
     */
    public function getFormAction()
    {
        return $this->getUrl('*/*/import');
    }
}
