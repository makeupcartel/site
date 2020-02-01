<?php
namespace Convert\OldOrder\Block\Adminhtml\OldOrder\View;

use Magento\Backend\Block\Widget\Form\Container;

Class View extends Container
{
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
	/**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
	protected function _construct()
	{
		$this->_blockGroup = "Convert_OldOrder";
		$this->_controller = "adminhtml_oldorder";
		$this->_headerText = __('Old Order');
       
		$this->_mode = 'view';
		$this->_objectId = 'id';
		parent::_construct();
		
		$this->removeButton('delete');
		$this->removeButton('reset');
		$this->removeButton('save');
		$this->setId('sales_old_order_view');
		$order = $this->getOrder();
		
		if (!$order) {
            return;
		}
		
	}
	/**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('old_order');
	}
	
}