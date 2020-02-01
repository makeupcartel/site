<?php
namespace Convert\SkinQuiz\Ui\Component;

use Convert\SkinQuiz\Model\ResourceModel\Quiz\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $employeeCollectionFactory
     * @param array $meta
     * @param array $data
     */
    protected $_loadedData;
    protected $jsonHelper;
    protected $eavConfig;
    protected $attributeRepository;
    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $QuizCollectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        array $meta = [],
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) 
    {
        $this->jsonHelper = $jsonHelper;
        $this->eavConfig  = $eavConfig;
        $this->collection = $QuizCollectionFactory->create();
        $this->attributeRepository = $attributeRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) 
        {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $quiz) 
        {
            $data = json_decode($quiz->getData('guestquiz'), TRUE);
            $data2 = $data;
            $dataSkinConcern = array_values($data2['skin-concern']);
            $newSkinConcernValue = $this->processMultiselectValue($data['skin-concern']);
            $newSkinConcernValueDehydration = $this->getOptionIdCustomer('skin_concern_account_dehydration',$dataSkinConcern[0]);
            $newSkinConcernValueFinelines = $this->getOptionIdCustomer('skin_concern_account_finelines',$dataSkinConcern[1]);
            $newSkinConcernValueCongestion = $this->getOptionIdCustomer('skin_concern_account_congestion',$dataSkinConcern[2]);
            $newSkinConcernValuePigmentation = $this->getOptionIdCustomer('skin_concern_account_pigmentation',$dataSkinConcern[3]);
            $newSkinConcernValueCoarse = $this->getOptionIdCustomer('skin_concern_account_coarse',$dataSkinConcern[4]);
            $newSkinConcernValueAcne = $this->getOptionIdCustomer('skin_concern_account_acne',$dataSkinConcern[5]);
            $newSkinSensitivityValue = $this->processMultiselectValue($data['skin-sensitivity']);
            unset($data['form_key']);
            unset($data['skin-concern']);
            $data['skin-concern'] = $newSkinConcernValue;
            $data['skin-concern-dehydration'] = $newSkinConcernValueDehydration;
            $data['skin-concern-finelines'] = $newSkinConcernValueFinelines;
            $data['skin-concern-congestion'] = $newSkinConcernValueCongestion;
            $data['skin-concern-pigmentation'] = $newSkinConcernValuePigmentation;
            $data['skin-concern-coarse'] = $newSkinConcernValueCoarse;
            $data['skin-concern-acne'] = $newSkinConcernValueAcne;
            unset($data['skin-sensitivity']);
            $data['skin-sensitivity'] = $newSkinSensitivityValue;
            $this->_loadedData[$quiz->getId()] = $data;
        }
        return $this->_loadedData;
    }

    private function processMultiselectValue($array)
    {
        $result = [];
        if(is_array($array)){
            foreach ($array as $key => $value) {
                if($value > 0) $result[] = $key;
            }

            $result = implode(',', $result);
        }

        return $result;
    }

    private function retrieveOptions($attributeCode)
    {
        $attribute = $this->eavConfig->getAttribute('customer', $attributeCode);

        return $attribute->getSource()->getAllOptions();
    }

    public function getOptionIdCustomer($codeattribuite, $label)
    {
        $attribute = $this->attributeRepository->get('customer', $codeattribuite);
        return $attribute->getSource()->getOptionId($label);
    }

}

