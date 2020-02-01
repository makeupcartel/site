<?php

namespace Convert\SkinQuiz\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Filter
{
    const SKIN_TYPE_ATTRIBUTE = "skin_type_quiz";
    const SKIN_CONCERN_ATTRIBUTE = "skin_concern_filter";
    const SKIN_SENSITIVITY_ATTRIBUTE = "medically_diagnosed_filter";

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    protected $_productloader;  
    protected $eavConfig;

	public function __construct(
        CollectionFactory $collectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ProductFactory $_productloader

	)
	{
        $this->collectionFactory = $collectionFactory;
        $this->eavConfig = $eavConfig;
        $this->_productloader = $_productloader;
    }
    
    public function getResult($params)
    {
        
        $productCollection = $this->getCollection();
        $filteredCollection = NULL;

        if($params && array_key_exists('skin-sensitivity', $params)) {
            if($this->isNoMed($params)){
                $filteredCollection = $this->getResultFilterNoMed($productCollection, $params);
            } else {
                $filteredCollection = $this->getResultFilterWithMed($productCollection, $params);
            }
        }
        
        return $filteredCollection;
    }

    public function getSkinTypeResult($params)
    {
        if($params && array_key_exists('skin-sensitivity', $params)) {
            $sortedSkinConcern = $this->sortSkinConcern($params['skin-concern']);
            $skinTypeResults = [];
            if($this->isNoMed($params)){
                if($params['skin-type'] == 1 || $params['skin-type'] == 2) {
                    $skinType = 'dry';
                } elseif ($params['skin-type'] == 3) {
                    $skinType = 'normal';
                } else {
                    $skinType = 'oily';
                }
                foreach ($sortedSkinConcern as $concern) {
                    $skinTypeResults[] = 'quiz-' . $skinType . '-' . $concern;
                }
            } else {
                foreach ($sortedSkinConcern as $concern) {
                    $skinTypeResults[] = 'quiz-' . $concern . '-med';
                }
            }
        }

        return $skinTypeResults;
    }

    public function getSkinTypeTitle($params)
    {
        if($params && array_key_exists('skin-sensitivity', $params)) {
            $skinTypeOptions = $this->retrieveOptions(self::SKIN_TYPE_ATTRIBUTE);
            return $skinTypeOptions[$params['skin-type']]['label'];
        }
    }

    private function sortSkinConcern($skinConcernArray)
    {
        arsort($skinConcernArray);

        $sortKeys = [];

        foreach ($skinConcernArray as $skinConcernKey => $skinConcernValue) {
            if($skinConcernValue > 0) {
                $sortKeys[] = $skinConcernKey;
            }
        }

        $skinConcernOptions = $this->retrieveOptions(self::SKIN_CONCERN_ATTRIBUTE);

        $sortedArray = [];

        foreach ($sortKeys as $key) {
            foreach($skinConcernOptions as $option) {
                if($key == $option['value']) {
                    $sortedArray[] = $this->clean($option['label']);
                }
            }
        }

        return $sortedArray;
    }

    private function clean($string) {
        $string = strtolower($string); // To lowercase
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
     
        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
     }

    private function getResultFilterNoMed($collection, $params)
    {
        $skinConcernFilterArray = [];

        foreach ($params['skin-concern'] as $skinConcernKey => $skinConcernValue) {
            if($skinConcernValue > 0) {
                $skinConcernFilterArray[] = array('finset'=> array($skinConcernKey));
            }
        }

        $skinTypeOptions = $this->retrieveOptions(self::SKIN_TYPE_ATTRIBUTE);

        if($params['skin-type'] == 1 || $params['skin-type'] == 2) {
            $skinTypeFilterArray = array('finset' => $skinTypeOptions['1']['value']);
        } elseif ($params['skin-type'] == 3) {
            $skinTypeFilterArray = array('finset' => $skinTypeOptions['3']['value']);
        } else {
            $skinTypeFilterArray = array('finset' => $skinTypeOptions['5']['value']);
        }
        
        if ($skinConcernFilterArray) {
            $collection->addAttributeToSelect('*')
                ->addAttributeToFilter(self::SKIN_CONCERN_ATTRIBUTE, $skinConcernFilterArray)
                ->addAttributeToFilter(self::SKIN_TYPE_ATTRIBUTE, $skinTypeFilterArray);
        } else {
            return false;
        }    

        // $sortString = $this->createArrayFieldSort($params['skin-concern'], $collection->getData());
        $sortString = $this->createArrayProductSort($params['skin-concern'], $collection->getData(), $params);

        $collection->getSelect()->order(new \Zend_Db_Expr("FIELD(entity_id,$sortString)"));
        return $collection;
    }

    private function getResultFilterWithMed($collection, $params)
    {
        $skinConcernFilterArray = [];

        foreach ($params['skin-concern'] as $skinConcernKey => $skinConcernValue) {
            if($skinConcernValue > 0) {
                $skinConcernFilterArray[] = array('finset'=> array($skinConcernKey));
            }
        }
        
        if ($skinConcernFilterArray) {
            $collection->addAttributeToSelect('*')
                ->addAttributeToFilter(self::SKIN_CONCERN_ATTRIBUTE, $skinConcernFilterArray)
                ->addAttributeToFilter(self::SKIN_SENSITIVITY_ATTRIBUTE, array('notnull' => true));
        } else {
            return false;
        }

        // $sortString = $this->createArrayFieldSort($params['skin-concern'], $collection->getData());
        $sortString = $this->createArrayProductSort($params['skin-concern'], $collection->getData(), $params);

        $collection->getSelect()->order(new \Zend_Db_Expr("FIELD(entity_id,$sortString)"));
        
        return $collection;
    }

    private function createArrayFieldSort($skinConcernArray, $productData)
    {
        $sortKeys = [];

        foreach ($skinConcernArray as $skinConcernKey => $skinConcernValue) {
            if($skinConcernValue > 0) {
                $sortKeys[] = $skinConcernKey;
            }
        }

        $sortArray = [];

        foreach ($sortKeys as $key) {
            foreach($productData as $product) {
                $productSkinConcernArr = explode(',',$product['skin_concern_filter']);
                if(in_array($key, $productSkinConcernArr) && !in_array($product['entity_id'], $sortArray)) {
                    $sortArray[] = $product['entity_id'];
                }
            }
        }

        return implode(",", $sortArray);
    }

    private function createArrayProductSort($skinConcernArray, $productData, $params)
    {
        $sortKeys = [];
        foreach ($skinConcernArray as $skinConcernKey => $skinConcernValue) 
        {
            if($skinConcernValue > 0) {
                $sortKeys[] = $skinConcernKey;
            }
        }
        $sortArray = [];
        foreach ($sortKeys as $key) 
        {
            foreach($productData as $product) 
            {
                $productSkinConcernArr = explode(',',$product['skin_concern_filter']);
                $skinExclude = $this->getValueAtribuite($product['entity_id'], 'skin_quiz_exclude');
                $skinExcludeArr = explode(',',$skinExclude);
                $skinTypeResultsConcern  = $this->getSkinResultSkinConcern($key, $params);
                if(in_array($key, $productSkinConcernArr) && !in_array($product['entity_id'], $sortArray) && in_array($skinTypeResultsConcern, $skinExcludeArr)) 
                {
                    $sortArray[] = $product['entity_id'];
                }
            }
        }
        return implode(",", $sortArray);
    }

    private function isNoMed($params)
    {
        foreach ($params['skin-sensitivity'] as $param) {
            if($param) return false;
        }
        return true;
    }

    private function retrieveOptions($attributeCode)
	{
		$attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

		return $attribute->getSource()->getAllOptions();
	}

    private function getCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();

        return $collection;
    }

    private function getValueAtribuite($idProduct, $attributeCode)
    {
        $product = $this->_productloader->create()->load($idProduct);
        if (null !== $product->getCustomAttribute($attributeCode)) {
            return $product->getCustomAttribute($attributeCode)->getValue();
        }else{
            return null;
        }
    }

    private function getSkinResultSkinConcern($key, $params)
    {
        $skinTypeConcern = '';
        $concern = $this->getLabelOption('skin_concern_filter', $key);
        if ($concern != '') {
            $concern = $this->clean($concern);
        }

        if($this->isNoMed($params)){
            if($params['skin-type'] == 1 || $params['skin-type'] == 2) {
                $skinType = 'dry';
            } elseif ($params['skin-type'] == 3) {
                $skinType = 'normal';
            } else {
                $skinType = 'oily';
            }
            $skinTypeConcern = 'quiz-' . $skinType . '-' . $concern;
            return $skinTypeConcern;
        }else{
            $skinTypeConcern = 'quiz-' . $concern . '-med';
            return $skinTypeConcern;
        }
    }

    private function getLabelOption($attributeCode, $optionId)
    {
        $_product = $this->_productloader->create();
        $isAttributeExist = $_product->getResource()->getAttribute($attributeCode); 
        $optionText = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionText = $isAttributeExist->getSource()->getOptionText($optionId);
        }
        return $optionText;
    }
}
