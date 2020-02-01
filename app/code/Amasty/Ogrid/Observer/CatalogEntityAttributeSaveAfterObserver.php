<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Observer;

use Magento\Framework\Event\ObserverInterface;

class CatalogEntityAttributeSaveAfterObserver implements ObserverInterface
{
    protected $_attributeFactory;
    protected $_productAttributesIndexerProcessor;

    public function __construct(
        \Amasty\Ogrid\Model\AttributeFactory $attributeFactory,
        \Amasty\Ogrid\Model\Indexer\Attribute\Processor $productAttributesIndexerProcessor
    ){
        $this->_attributeFactory = $attributeFactory;
        $this->_productAttributesIndexerProcessor = $productAttributesIndexerProcessor;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $catalogAttribute = $observer->getEvent()->getData('data_object');
        if ($catalogAttribute){
            $attribute = $this->_attributeFactory->create()->load($catalogAttribute->getId(), 'attribute_id');

            if ($catalogAttribute->getData('amasty_ogrid_use_in_index')) {
                $attribute->addData([
                    'attribute_id' => $catalogAttribute->getId(),
                    'attribute_code' => $catalogAttribute->getAttributeCode(),
                    'frontend_label' => $catalogAttribute->getFrontendLabel(),
                ]);

                $attribute->save();

                $this->_productAttributesIndexerProcessor->markIndexerAsInvalid();
            } else {
                $attribute->delete();
            }
        }
    }
}
