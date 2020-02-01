<?php

namespace Convert\SkinQuiz\Model\Grid;

use Convert\SkinQuiz\Model\ResourceModel\Quiz\Collection;

class SkinSensitivity extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	protected $eavConfig;
	
	function __construct(\Magento\Eav\Model\Config $eavConfig)
	{
		$this->eavConfig = $eavConfig;
	}


	public function getAllOptions()
	{
		if (!$this->_options) {
			$this->_options = $this->retrieveOptions('medically_diagnosed_filter');
        }
        
		return $this->_options;
	}

	private function retrieveOptions($attributeCode)
	{
		$attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

		return $attribute->getSource()->getAllOptions();
	}
}