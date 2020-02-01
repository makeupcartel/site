<?php
namespace Convert\PersistentCustomer\Plugin\Quote;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ShippingMethodManagement
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * Customer Address repository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $_customerFactory;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->checkoutSession = $checkoutSession;
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Add customers address type to shipping address on quote
     *
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param                                               $cartId
     * @param int                                           $addressId
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeEstimateByAddressId(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        $cartId,
        $addressId
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        $quoteAddress = $quote->getShippingAddress();

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [$cartId, $addressId];
        }
        try {
            /** @var  \Magento\Customer\Model\Data\Address $address */
            $address = $this->addressRepository->getById($addressId);
        } catch (NoSuchEntityException $e) {
            return [$cartId, $addressId];
        }

        /**
         * SHQ18-993 Reset so values from previously selected address aren't carrier over
         */
        $quoteAddress->unsetData('destination_type');
        $quoteAddress->unsetData('validation_status');

        if ($custom = $address->getCustomAttributes()) {
            foreach ($custom as $custom_attribute) {
                if ($custom_attribute->getAttributeCode() == 'destination_type') {
                    $quoteAddress->setData('destination_type', $custom_attribute->getValue());
                } elseif ($custom_attribute->getAttributeCode() == 'validation_status') {
                    $quoteAddress->setData('validation_status', $custom_attribute->getValue());
                }
            }
        }

        return [$cartId, $addressId];
    }

    /**
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param $cartId
     * @param AddressInterface $address
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeEstimateByExtendedAddress(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [$cartId, $address];
        }
        //if logged in, get the default address and apply address type to address
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            if ($defaultShipping = $customer->getDefaultShipping()) {
                try {
                    /** @var  \Magento\Customer\Model\Data\Address $address */
                    $defaultAddress = $this->addressRepository->getById($defaultShipping);
                } catch (NoSuchEntityException $e) {
                    //get customer model before you can get its address data
                    $customer = $this->_customerFactory->create()->load($this->customerSession->getCustomerId());    //insert customer id
                    $customer->setDefaultShipping(null)->save();
                    return [$cartId, $address];
                }
                
                if ($custom = $defaultAddress->getCustomAttributes()) {
                    foreach ($custom as $custom_attribute) {
                        if ($custom_attribute->getAttributeCode() == 'destination_type') {
                            $quote->getShippingAddress()->setData('destination_type', $custom_attribute->getValue());
                        }
                    }
                }
            }
        }

        return [$cartId, $address];
    }
}
