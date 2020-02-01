<?php

namespace Convert\SkinQuiz\Block;

use Magento\Framework\View\Element\Template;

class Form extends Template
{
	protected $eavConfig;
	protected $_customerSession;
	protected $currentCustomer;

	public function __construct(
		Template\Context $context,
		\Magento\Eav\Model\Config $eavConfig,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
		array $data = []
	)
	{
		$this->eavConfig = $eavConfig;
		$this->_customerSession = $customerSession;
		$this->currentCustomer = $currentCustomer;
		parent::__construct($context, $data);
	}

	public function retrieveOptions($attributeCode)
	{
		$attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
		return $attribute->getSource()->getAllOptions();
	}

	public function checkLogin()
	{
		if ($this->_customerSession->isLoggedIn()) 
		{
			return 'customer';
		} else 
		{
			return 'guest';
		}
	}

		public function getCustomerEmail()
	{
		$email = '';
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$customerSession = $objectManager->create('Magento\Customer\Model\Session');

		if ($this->_customerSession->isLoggedIn()) 
		{
			 $email = $customerSession->getCustomerData()->getEmail();
		}
		 return $email;
	}


	public function getCustomer()
	{
		try {
			return $this->currentCustomer->getCustomer();
		} catch (NoSuchEntityException $e) {
			return null;
		}
	}
}
