<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Backend\Block\Template;
use Unirgy\RapidFlow\Model\Profile;

/**
 * Class Status
 *
 * @method Profile getProfile()
 * @package Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab
 */
class Status extends Template
{
    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Unirgy_RapidFlow::urapidflow/status/tab.phtml');
    }
}
