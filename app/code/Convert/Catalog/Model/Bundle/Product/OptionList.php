<?php

namespace Convert\Catalog\Model\Bundle\Product;

/**
 * Class OptionList
 *
 * @package Convert\Catalog\Model\Bundle\Product
 */
class OptionList extends \Magento\Bundle\Model\Product\OptionList
{
    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    public function getItems(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $optionCollection = $this->type->getOptionsCollection($product);
        $this->extensionAttributesJoinProcessor->process($optionCollection);
        $optionList = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionCollection as $option) {
            $productLinks = $this->linkList->getItems($product, $option->getOptionId());
            /** @var \Magento\Bundle\Api\Data\OptionInterface $optionDataObject */
            $optionDataObject = $this->optionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $optionDataObject,
                $option->getData(),
                \Magento\Bundle\Api\Data\OptionInterface::class
            );
            $optionDataObject->setOptionId($option->getOptionId())
                ->setTitle($option->getTitle() === null ? $option->getDefaultTitle() : $option->getTitle())
                ->setDefaultTitle($option->getDefaultTitle())
                ->setSku($product->getSku())
                ->setMaxOption($option->getMaxOption())
                ->setProductLinks($productLinks);
            $optionList[] = $optionDataObject;
        }
        return $optionList;
    }
}
