<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Controller\Adminhtml\Massaction\Attribute;

use Amasty\Oaction\Model\OrderAttributesChecker;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var OrderAttributesChecker
     */
    private $attributesChecker;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        TimezoneInterface $timezone,
        OrderAttributesChecker $attributesChecker
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
        $this->attributesChecker = $attributesChecker;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(
            'sales/order',
            ['_current' => true]
        );

        try {
            if ($this->attributesChecker->isModuleExist()) {
                $attributes = $this->getRequest()->getParam('attributes');
                $orderIds = $this->_objectManager->get(\Amasty\Orderattr\Model\Order\Storage::class)->getOrderIds();

                if ($orderIds && $attributes) {
                    foreach ($orderIds as $orderId) {
                        $this->saveAttributesForOrderByOrderId($orderId, $attributes);
                    }
                }
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect;
    }

    /**
     * @param $orderId
     * @param $attributes
     *
     * @return bool
     */
    private function saveAttributesForOrderByOrderId($orderId, $attributes)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
            $data = $order->getData();
            $entity = $this->_objectManager
                ->get(\Amasty\Orderattr\Model\Entity\EntityResolver::class)->getEntityByOrder($order);
            $extensionAttributes = $data['extension_attributes']->__toArray();
            $orderAttributesExtensionAttribute = !isset($extensionAttributes['amasty_order_attributes'])
                    ? [] : $extensionAttributes['amasty_order_attributes'];

            $attributes = $this->updateAttributes(
                $orderAttributesExtensionAttribute,
                $attributes
            );

            if (!$attributes) {
                $this->messageManager->addErrorMessage(
                    __('The attributes for next order: %1 have not been updated.', $order->getIncrementId())
                );

                return false;
            }


            $form = $this->createEntityForm($entity, $order->getStoreId(), $order->getCustomerGroupId());
            // emulate request
            $request = $form->prepareRequest($attributes);
            $data = $form->extractData($request);
            $entity->setCustomAttributes([]);
            $form->restoreData($data);

            $this->_objectManager->get(\Amasty\Orderattr\Model\Entity\Handler\Save::class)->execute($entity);
            $this->messageManager->addSuccessMessage(
                __('The attributes for next order: %1 have been updated.', $order->getIncrementId())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('An error occurred while updating the order attributes: ' . $e->getMessage())
            );
        }
    }

    /**
     * @param $currentAttributes
     * @param $updatedAttributes
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateAttributes($currentAttributes, $updatedAttributes)
    {
        if ($updatedAttributes) {
            if (!$currentAttributes) {
                return $updatedAttributes;
            }

            foreach ($currentAttributes as $key => $attribute) {
                $attributeCode = $attribute['attribute_code'];

                if ($this->checkIsDeleteAttribute($attributeCode, $updatedAttributes)) {
                    $attribute['value'] = '';
                }

                if (isset($updatedAttributes[$attribute['attribute_code']])) {
                    $attribute['value'] = $updatedAttributes[$attribute['attribute_code']];
                    unset($updatedAttributes[$attribute['attribute_code']]);
                }

                $currentAttributes[$attribute['attribute_code']] = $attribute['value'];
                unset($currentAttributes[$key]);
            }

            $currentAttributes = array_merge($currentAttributes, $updatedAttributes);

            return $currentAttributes;
        }

        return false;
    }

    /**
     * @param string $attributeCode
     * @param array $updatedAttributes
     *
     * @return bool
     */
    private function checkIsDeleteAttribute($attributeCode, $updatedAttributes)
    {
        $allData = $this->getRequest()->getParams();
        $checkboxName = 'toggle_' . $attributeCode;

        return isset($allData[$checkboxName]) && $allData[$checkboxName] == 'on'
            && !isset($updatedAttributes[$attributeCode]);

    }

    /**
     * Return Checkout Form instance
     *
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     * @param int $store
     * @param int $customerGroup
     *
     * @return Form
     */
    private function createEntityForm($entity, $store, $customerGroup)
    {
        /** @var \Amasty\Orderattr\Model\Value\Metadata\Form $formProcessor */
        $formProcessor = $this->_objectManager->create(\Amasty\Orderattr\Model\Value\Metadata\Form::class);
        $formProcessor->setFormCode('adminhtml_checkout')
            ->setEntity($entity)
            ->setStore($store)
            ->setCustomerGroupId($customerGroup);

        return $formProcessor;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Oaction::oaction');
    }
}
