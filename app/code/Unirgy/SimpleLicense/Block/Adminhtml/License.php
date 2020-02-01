<?php
/**
 * \Unirgy\StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    \Unirgy\SimpleUp
 * @copyright  Copyright (c) 2011 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    \Unirgy\SimpleUp
 * @author     Boris (Moshe) Gurvich <support@unirgy.com>
 */
namespace Unirgy\SimpleLicense\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class License extends Container
{
    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'usimplelic';
        $this->_controller = 'adminhtml_license';
        $this->buttonList->remove('add');
        $this->setTemplate('usimplelic/container.phtml');
    }
}
