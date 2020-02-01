<?php

namespace Convert\Checkout\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Class ShippingInformationManagement
 *
 * @package Convert\Checkout\Model
 */
class ShippingInformationManagement extends \Magento\Checkout\Model\ShippingInformationManagement
{
    /**
     * @var CartExtensionFactory
     */
    protected $_cartExtensionFactory;

    /**
     * @var ShippingFactory
     */
    protected $_shippingFactory;

    /**
     * Save address information.
     *
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function saveAddressInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $this->validateQuote($quote);
        if($quote->getCustomerId()){
            $quote->setCustomerIsGuest(null);
        }
        $address = $addressInformation->getShippingAddress();
        if (!$address || !$address->getCountryId()) {
            throw new StateException(__('The shipping address is missing. Set the address and try again.'));
        }
        if (!$address->getCustomerAddressId()) {
            $address->setCustomerAddressId(null);
        }

        try {
            $billingAddress = $addressInformation->getBillingAddress();
            if ($billingAddress) {
                if (!$billingAddress->getCustomerAddressId()) {
                    $billingAddress->setCustomerAddressId(null);
                }
                $this->addressValidator->validateForCart($quote, $billingAddress);
                $quote->setBillingAddress($billingAddress);
            }

            $this->addressValidator->validateForCart($quote, $address);
            $carrierCode = $addressInformation->getShippingCarrierCode();
            $address->setLimitCarrier($carrierCode);
            $methodCode = $addressInformation->getShippingMethodCode();
            $quote = $this->prepareShippingAssignment($quote, $address, $carrierCode . '_' . $methodCode);

            $quote->setIsMultiShipping(false);

            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(
                __('The shipping information was unable to be saved. Verify the input data and try again.')
            );
        }

        $shippingAddress = $quote->getShippingAddress();

        if (!$shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod())) {
            throw new NoSuchEntityException(
                __('Carrier with such method not found: %1, %2', $carrierCode, $methodCode)
            );
        }

        /** @var \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
        return $paymentDetails;
    }

    /**
     * Prepare shipping assignment.
     *
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @param string $method
     * @return CartInterface
     */
    private function prepareShippingAssignment(CartInterface $quote, AddressInterface $address, $method)
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->getCartExtensionFactory()->create();
        }
        $shippingAssignments = $cartExtension->getShippingAssignments();
        if (empty($shippingAssignments)) {
            $shippingAssignment = $this->shippingAssignmentFactory->create();
        } else {
            $shippingAssignment = $shippingAssignments[0];
        }
        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            $shipping = $this->getShippingFactory()->create();
        }
        $shipping->setAddress($address);
        $shipping->setMethod($method);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quote->setExtensionAttributes($cartExtension);
    }

    /**
     * @return CartExtensionFactory
     */
    protected function getShippingFactory()
    {
        if (!$this->_cartExtensionFactory) {
            $this->_cartExtensionFactory = ObjectManager::getInstance()->get(CartExtensionFactory::class);
        }
        return $this->_cartExtensionFactory;
    }

    /**
     * @return ShippingFactory
     */
    protected function getCartExtensionFactory()
    {
        if (!$this->_shippingFactory) {
            $this->_shippingFactory = ObjectManager::getInstance()->get(ShippingFactory::class);
        }
        return $this->_shippingFactory;
    }
}
