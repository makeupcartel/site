<?php 
namespace Convert\SkinQuiz\Model;

/**
 * 
 */
class Customerquiz
{
	protected $attributeRepository;
	protected $_customer;
	protected $eavConfig;
	protected $_customerFactory;
	function __construct(
		\Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
		\Magento\Customer\Model\Customer $customer,
		\Magento\Eav\Model\Config $eavConfig,
		\Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory
	)
	{
		$this->attributeRepository = $attributeRepository;
		$this->_customer = $customer;
		$this->_customerFactory = $customerFactory;
		$this->eavConfig = $eavConfig;
	}

	public function setAtribuiteQuizCustomer($id, $age, $arraysensitivity, $typeskinvalue, $skinconcern)
	{
		$arraylabelskinconcern = array_values($skinconcern);
		$arraysensitivitylabel = array_values($arraysensitivity);
		$idAge = $this->getOptionIdCustomer('age_range', $age);
		$iddehydration = $this->getOptionIdCustomer('skin_concern_account_dehydration', $arraylabelskinconcern[0]);
		$idfineline = $this->getOptionIdCustomer('skin_concern_account_finelines', $arraylabelskinconcern[1]);
		$idcongestion = $this->getOptionIdCustomer('skin_concern_account_congestion', $arraylabelskinconcern[2]);
		$idhyperpigmentation = $this->getOptionIdCustomer('skin_concern_account_pigmentation', $arraylabelskinconcern[3]);
		$idcoarse = $this->getOptionIdCustomer('skin_concern_account_coarse', $arraylabelskinconcern[4]);
		$idacne = $this->getOptionIdCustomer('skin_concern_account_acne', $arraylabelskinconcern[5]);
		$typeskinlabel = $this->retrieveOptions('skin_type_quiz');
		$idskintype = $this->getOptionIdCustomer('skin_type_account', $typeskinlabel[$typeskinvalue]['label']);

        //load customer by id
		$customerload = $this->_customer->load($id);
		$customerData = $customerload->getDataModel();
		$customerData->setCustomAttribute('age_range', $idAge)
		->setCustomAttribute('psoriasis_med_skin_concern', $arraysensitivitylabel[0])
		->setCustomAttribute('eczema_med_skin_concern', $arraysensitivitylabel[1])
		->setCustomAttribute('rosacea_med_skin_concern', $arraysensitivitylabel[2])
		->setCustomAttribute('dermatitis_med_skin_concern', $arraysensitivitylabel[3])
		->setCustomAttribute('severereaction_med_skin_concern', $arraysensitivitylabel[4])
		->setCustomAttribute('skin_type_account', $idskintype)
		->setCustomAttribute('skin_concern_account_dehydration', $iddehydration)
		->setCustomAttribute('skin_concern_account_finelines', $idfineline)
		->setCustomAttribute('skin_concern_account_congestion', $idcongestion)
		->setCustomAttribute('skin_concern_account_pigmentation', $idhyperpigmentation)
		->setCustomAttribute('skin_concern_account_coarse', $idcoarse)
		->setCustomAttribute('skin_concern_account_acne', $idacne);

		$customerload->updateData($customerData);
		$customerResource = $this->_customerFactory->create();
		return $customerResource->saveAttribute($customerload, 'age_range')
		->saveAttribute($customerload, 'psoriasis_med_skin_concern')
		->saveAttribute($customerload, 'eczema_med_skin_concern')
		->saveAttribute($customerload, 'rosacea_med_skin_concern')
		->saveAttribute($customerload, 'dermatitis_med_skin_concern')
		->saveAttribute($customerload, 'severereaction_med_skin_concern')
		->saveAttribute($customerload, 'skin_type_account')
		->saveAttribute($customerload, 'skin_concern_account_dehydration')
		->saveAttribute($customerload, 'skin_concern_account_finelines')
		->saveAttribute($customerload, 'skin_concern_account_congestion')
		->saveAttribute($customerload, 'skin_concern_account_pigmentation')
		->saveAttribute($customerload, 'skin_concern_account_coarse')
		->saveAttribute($customerload, 'skin_concern_account_acne');
	}

	public function getOptionIdCustomer($codeattribuite, $label)
	{
		$attribute = $this->attributeRepository->get('customer', $codeattribuite);
		return $attribute->getSource()->getOptionId($label);
	}

	public function retrieveOptions($custom_attribute_code)
	{
		$attribute = $this->eavConfig->getAttribute('catalog_product', $custom_attribute_code);
		$options = $attribute->getSource()->getAllOptions();

		$Optionarray=[];
		$a = 0;
		foreach ($options as $key) 
		{
			$Optionarray[$a]['label'] = $key['label'];
			$Optionarray[$a]['value'] = $key['value'];
			$a++;	
		}
		return $Optionarray;
	}
}