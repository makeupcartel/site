<?php 
namespace Convert\SkinQuiz\Observer;

/**
 * 
 */
class SaveSkinconcernCustomer implements \Magento\Framework\Event\ObserverInterface
{

	protected $_customerFactory;
	protected $_customer;

	public function __construct(
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
		\Magento\Customer\Model\Customer $customer
	)
	{
		$this->_request = $request;
		$this->_customerFactory = $customerFactory;
		$this->_customer = $customer;
	}
	
	public function execute(
		\Magento\Framework\Event\Observer $observer
	)
	{
		$idMySkinConcern = $this->_request->getParam('my_skin_concern'); 
		$customerId = $observer->getEvent()->getCustomer()->getId();
		if ($customerId) 
		{
			$customerload = $this->_customer->load($customerId);
			$customerData = $customerload->getDataModel();
			$customerData->setCustomAttribute('my_skin_concern', $idMySkinConcern);
			$customerload->updateData($customerData);
			$customerResource = $this->_customerFactory->create();
			return $customerResource->saveAttribute($customerload,'my_skin_concern');
		}
		return false;
	}
}