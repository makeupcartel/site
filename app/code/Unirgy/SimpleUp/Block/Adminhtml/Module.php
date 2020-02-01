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
namespace Unirgy\SimpleUp\Block\Adminhtml;

use \Magento\Backend\Block\Widget\Context;
use \Magento\Backend\Block\Widget\Form\Container;
use \Magento\Framework\Registry;


class Module extends Container
{
    public function __construct(Context $context,
                                Registry $registry,
                                array $data = [])
    {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_headerText = __('Unirgy Installer');

        $this->_objectId = 'id';
        $this->_blockGroup = 'usimpleup';
        $this->_controller = 'adminhtml_module';
        parent::_construct();

        $this->buttonList->add('check_updates', [
            'label' => __('Check for version updates'),
            'onclick' => "location.href = '{$this->getUrl('usimpleup/module/checkUpdates')}'",
            'class' => 'save',
        ], 0);

        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('back');
    }
}
