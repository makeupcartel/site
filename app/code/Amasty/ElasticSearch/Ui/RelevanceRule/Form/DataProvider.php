<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Ui\RelevanceRule\Form;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Amasty\ElasticSearch\Model\Source\RelevanceRuleModificationType;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    public function __construct(
        \Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $result = [];

        /** @var RelevanceRuleInterface $item */
        foreach ($this->collection as $item) {
            $result[$item->getId()] = $item->getData();
            if ($item->getMultiplier() < 0) {
                $result[$item->getId()][RelevanceRuleInterface::MULTIPLIER] = abs($item->getMultiplier());
                $result[$item->getId()]['modification_type'] = RelevanceRuleModificationType::DECREASE;
            } else {
                $result[$item->getId()]['modification_type'] = RelevanceRuleModificationType::INCREASE;
            }
        }

        return $result;
    }
}
