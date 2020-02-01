<?php

namespace Convert\BookSkin\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Magento\Variable\Model\VariableFactory;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Contact\Model\ConfigInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class BookSkin
 *
 * @package Convert\BookSkin\Block
 */
class BookSkin extends Template
{
    const XML_PATH_ENABLED = ConfigInterface::XML_PATH_ENABLED;

    /**
     * @var VariableFactory
     */
	protected $_varFactory;

    /**
     * @var Session
     */
	protected $_customerSession;

    /**
     * @var CustomerViewHelper
     */
	protected $_customerViewHelper;

    /**
     * @var null
     */
	private $postData = null;

    /**
     * @var
     */
	private $dataPersistor;

    /**
     * BookSkin constructor.
     *
     * @param Template\Context $context
     * @param VariableFactory $varFactory
     * @param Session $customerSession
     * @param CustomerViewHelper $customerViewHelper
     * @param array $data
     */
	public function __construct(
		Template\Context $context,
		VariableFactory $varFactory,
        Session $customerSession,
		CustomerViewHelper $customerViewHelper,
        array $data = []
	)
	{
		$this->_varFactory = $varFactory;
		$this->_isScopePrivate = true;
		$this->_customerSession = $customerSession;
		$this->_customerViewHelper = $customerViewHelper;
		parent::__construct($context, $data);
	}

    /**
     * @return mixed
     */
	public function getSkinConcernValue()
	{
		return $this->_varFactory->create()->loadByCode('Skin-Concern')->getHtmlValue();
	}

    /**
     * @return mixed
     */
	public function getSkinTypeValue()
	{
		return $this->_varFactory->create()->loadByCode('Are-you-dry-or-oily')->getHtmlValue();
	}

    /**
     * @return string
     */
	public function getUserName()
	{
		if (!$this->_customerSession->isLoggedIn()) {
			return '';
		}
        /**
         * @var CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerDataObject();

        return trim($this->_customerViewHelper->getCustomerName($customer));
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
    	if (!$this->_customerSession->isLoggedIn()) {
    		return '';
    	}
        /**
         * @var CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerDataObject();

        return $customer->getEmail();
    }

    /**
     * @param $key
     * @return string
     */
    public function getPostValue($key)
    {
    	if (null === $this->postData) {
    		$this->postData = (array) $this->getDataPersistor()->get('book_skin');
    		$this->getDataPersistor()->clear('book_skin');
    	}

    	if (isset($this->postData[$key])) {
    		return (string) $this->postData[$key];
    	}

    	return '';
    }

    /**
     * @return mixed
     */
    private function getDataPersistor()
    {
    	if ($this->dataPersistor === null) {
    		$this->dataPersistor = ObjectManager::getInstance()
    		->get(DataPersistorInterface::class);
    	}

    	return $this->dataPersistor;
    }
}
