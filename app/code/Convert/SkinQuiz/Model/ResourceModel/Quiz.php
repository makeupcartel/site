<?php
namespace Convert\SkinQuiz\Model\ResourceModel;
class Quiz extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	protected function _construct(){
		$this->_init("quiz_customer","id");
	}
}