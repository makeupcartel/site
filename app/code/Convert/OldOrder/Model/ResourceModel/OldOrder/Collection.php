<?php
namespace Convert\OldOrder\Model\ResourceModel\OldOrder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

	protected function _construct()
	{
		$this->_init('Convert\OldOrder\Model\OldOrder', 'Convert\OldOrder\Model\ResourceModel\OldOrder');
	}
}