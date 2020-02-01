<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Attributes
 * @package Amasty\Reports\Model\Source
 */
class Attributes implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $eavCollection;

    public function __construct(
        CollectionFactory $eavCollection
    ) {
        $this->eavCollection = $eavCollection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->eavCollection->create();
        $collection->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 4);
        $allAttributes = $collection->load()->getItems();
        foreach ($allAttributes as $attribute) {
            $attributes[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel()
            ];
        }

        return $attributes;
    }
}
