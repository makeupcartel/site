<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Controller\Adminhtml\Massaction\Attribute;

class Validate extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Amasty\Oaction\Model\OrderAttributesChecker
     */
    private $orderAttributesChecker;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Amasty\Oaction\Model\OrderAttributesChecker $orderAttributesChecker
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eavConfig = $eavConfig;
        $this->orderAttributesChecker = $orderAttributesChecker;
    }

    /**
     * Attributes validation action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = $this->dataObjectFactory->create();
        $response->setError(false);
        $attributesData = $this->getRequest()->getParam('attributes', []);

        try {
            if ($this->orderAttributesChecker->isModuleExist() && $attributesData) {
                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = $this->eavConfig
                        ->getAttribute(
                            \Amasty\Orderattr\Model\ResourceModel\Entity\Entity::ENTITY_TYPE_CODE,
                            $attributeCode
                        );
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);

                        continue;
                    }
                }
            }
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessage($e->getMessage());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
            $layout = $this->layoutFactory->create();
            $layout->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());
        }

        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
