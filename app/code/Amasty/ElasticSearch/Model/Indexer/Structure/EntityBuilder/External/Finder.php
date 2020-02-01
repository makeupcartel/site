<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\External;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\EntityBuilderInterface;

class Finder implements EntityBuilderInterface
{
    /**
     * @return array
     */
    public function buildEntityFields()
    {
        $result = [];
        foreach ($this->getExtraValueFields() as $code => $type) {
            $result[$code . '_value'] = ['type' => $type];
        }

        return $result;
    }

    /**
     * Get fields for +'_value' replication.
     *
     * Keep it public in a case of customizations via plugin
     * @return string[]
     */
    public function getExtraValueFields()
    {
        return [
            'sku' => 'keyword'
        ];
    }
}
