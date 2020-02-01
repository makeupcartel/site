<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Structure;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\EntityBuilderInterface;

class EntityBuilder
{

    /**
     * @var EntityBuilderInterface[]
     */
    private $entityBuilders;

    public function __construct(
        array $entityBuilders = []
    ) {
        $this->entityBuilders = $entityBuilders;
    }

    /**
     * @param string $indexerId
     * @return array
     */
    public function build($indexerId)
    {
        $fieldArray = [];
        if (isset($this->entityBuilders[$indexerId]) && is_array($this->entityBuilders[$indexerId])) {
            foreach ($this->entityBuilders[$indexerId] as $builder) {
                $fieldArray = array_merge($fieldArray, $builder->buildEntityFields());
            }
        }
        return $fieldArray;
    }
}
