<?php
namespace Convert\SkinQuiz\Model\ResourceModel\Quiz;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

	protected $_idFieldName = 'id';

	protected function _construct()
	{
		$this->_init("Convert\SkinQuiz\Model\Quiz","Convert\SkinQuiz\Model\ResourceModel\Quiz");
	}
}