<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\Adapter\Category\Mysql\Aggregation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * TODO remove unused class ( method run execute is not run)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        $select = $this->getSelect();

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
}
