<?php

namespace Convert\SkinQuiz\Model\Attribute\Source;

class SkinExclude extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $eavConfig;

    /**
     * Construct
     *
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig
    )
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        $skinResultList = [];
        $skinTypes = ['dry','normal','oily'];

        //Get skin concerns label value
        $skinConcernOptions = $this->retrieveOptions("skin_concern_filter");
        $skinConcerns = [];

        foreach($skinConcernOptions as $option) {
            if($option['value']) $skinConcerns[] = $this->clean($option['label']);
        }

        //Add value to option array
        foreach ($skinConcerns as $concern) {
            foreach ($skinTypes as $type) {
                $label = 'quiz-' . $type . '-' . $concern;
                $skinResultList[] = ['label'=>$label,'value'=>$label];
            }
            $skinResultList[] = ['label'=>'quiz-' . $concern . '-med','value'=>'quiz-' . $concern . '-med'];
        }

        if (!$this->_options) {
            $this->_options = $skinResultList;
        }

        return $this->_options;
    }

    private function retrieveOptions($attributeCode)
	{
		$attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

		return $attribute->getSource()->getAllOptions();
    }
    
    private function clean($string) {
        $string = strtolower($string); // To lowercase
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
     
        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }
}
