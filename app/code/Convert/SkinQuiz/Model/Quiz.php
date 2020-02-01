<?php 
namespace Convert\SkinQuiz\Model;
class Quiz extends \Magento\Framework\Model\AbstractModel{
	protected function _construct(){
		$this->_init("Convert\SkinQuiz\Model\ResourceModel\Quiz");
	}
}