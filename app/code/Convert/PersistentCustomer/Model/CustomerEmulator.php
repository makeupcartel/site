<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Convert\PersistentCustomer\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CustomerEmulator
 */
class CustomerEmulator extends \Magento\PersistentHistory\Model\CustomerEmulator
{
    /**
     * Emulate cutomer
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function emulate()
    {
        /** TODO DataObject should be initialized instead of CustomerModel after refactoring of segment_customer */
        $customerId = $this->_persistentSession->getSession()->getCustomerId();
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->_customerFactory->create()->load($customerId);
        $error = false;
        if ($defaultShipping = $customer->getDefaultShipping()) {
            try {
                /** @var  \Magento\Customer\Model\Data\Address $address */
                $address = $this->addressRepository->getById($defaultShipping);
            } catch (NoSuchEntityException $e) {
                $error = true;
                $customer->setDefaultShipping(null);
            }
            
        }

        if ($defaultBilling = $customer->getDefaultBilling()) {
            try {
                /** @var  \Magento\Customer\Model\Data\Address $address */
                $address = $this->addressRepository->getById($defaultBilling);
            } catch (NoSuchEntityException $e) {
                $error = true;
                $customer->setDefaultBilling(null);
            }
        }
        if($error){
            $customer->save();
        }
        parent::emulate();
    }
}
