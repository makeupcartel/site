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

namespace Unirgy\RapidFlow\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Container;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Profile extends Container
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->_controller = 'adminhtml_profile';
        $this->_blockGroup = 'Unirgy_RapidFlow';
        $this->_headerText = __('RapidFlow Profile Manager');
        $this->_addButtonLabel = __('Add Profile');

        parent::__construct($context, $data);

        if ($this->_scopeConfig->getValue('urapidflow/advanced/disable_changes')) {
            $this->buttonList->remove('add');
        }
    }
}
