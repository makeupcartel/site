<?php

namespace Convert\SkinQuiz\Model\Grid;

use Convert\SkinQuiz\Model\ResourceModel\Quiz\Collection;

class SkinType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	protected $eavConfig;
	
	function __construct(\Magento\Eav\Model\Config $eavConfig)
	{
		$this->eavConfig = $eavConfig;
	}


	public function getAllOptions()
	{
		if (!$this->_options) {
			$labelArray = $this->retrieveOptions('skin_type_quiz');
			$this->_options = [
                ['value' => '1', 'label' => __($labelArray[1]['label'])],
                ['value' => '2', 'label' => __($labelArray[2]['label'])],
				['value' => '3', 'label' => __($labelArray[3]['label'])],
				['value' => '4', 'label' => __($labelArray[4]['label'])],
				['value' => '5', 'label' => __($labelArray[5]['label'])]
            ];
		}
		return $this->_options;
	}

	private function retrieveOptions($attributeCode)
	{
		$attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

		return $attribute->getSource()->getAllOptions();
	}
}