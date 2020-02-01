<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Model\Indexer\Attribute\Action;

use Magento\Framework\App\ResourceConnection;

class ResolveProductIds
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * Getting array with pair values row_id => entity_id from catalog_product_entity table
     *
     * @param array $entityIds
     * @return array
     */
    public function getRowIdsFromEntityIds(array $entityIds)
    {
        $table = $this->resource->getTableName('catalog_product_entity');
        $select = $this->connection->select()
            ->from(
                ['e' => $table],
                ['entity_id', 'row_id']
            )->where('entity_id IN(?)', $entityIds);

        $rowsArray = $this->connection->fetchAll($select);
        $rowIds = [];
        foreach ($rowsArray as $row) {
            $rowIds[$row['row_id']] = $row['entity_id'];
        }

        return $rowIds;
    }
}
