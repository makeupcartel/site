<?php

namespace Convert\OldOrder\Model;

class OldOrder extends \Magento\Framework\Model\AbstractModel 
{
	public function _construct()
	{
		$this->_init("Convert\OldOrder\Model\ResourceModel\OldOrder");
	}
}