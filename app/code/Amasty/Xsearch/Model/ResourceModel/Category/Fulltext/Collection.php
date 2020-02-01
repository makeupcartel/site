<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\ResourceModel\Category\Fulltext;

use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\Response\QueryResponse;

class Collection extends \Magento\Catalog\Model\ResourceModel\Category\Collection
{
    /**
     * @var QueryResponse
     */
    protected $queryResponse;

    /**
     * @var string
     */
    private $queryText;

    /**
     * @var \Magento\Framework\Search\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /** @var string */
    private $searchRequestName;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Adapter
     */
    private $mysqlAdapter;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Search\Request\Builder $requestBuilder,
        \Magento\Framework\Search\Adapter\Mysql\Adapter $mysqlAdapter,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $searchRequestName = 'amasty_xsearch_category'
    ) {

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $connection
        );

        $this->requestBuilder = $requestBuilder;
        $this->searchRequestName = $searchRequestName;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->mysqlAdapter = $mysqlAdapter;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _renderFiltersBefore()
    {
        if ($this->queryText) {
            $this->requestBuilder->bindDimension('scope', $this->getStoreId());
            $this->requestBuilder->bind('search_term', $this->queryText);
            $this->requestBuilder->setRequestName($this->searchRequestName);
            $queryRequest = $this->requestBuilder->create();
            $this->queryResponse = $this->mysqlAdapter->query($queryRequest);
            $temporaryStorage = $this->temporaryStorageFactory->create();
            $table = $temporaryStorage->storeDocuments($this->queryResponse->getIterator());
            $this->getSelect()->joinInner(
                [
                    'search_result' => $table->getName(),
                ],
                'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
                []
            );
            $this->getSelect()
                ->order('search_result.' . TemporaryStorage::FIELD_SCORE . ' DESC')
                ->order("e.entity_id ASC");
        }

        parent::_renderFiltersBefore();
    }

    /**
     * @return array[]
     */
    public function getIndexFulltextValues()
    {
        $select = $this->getConnection()->select()
            ->from(
                ['posts_tags' => $this->getTable('amasty_xsearch_category_fulltext_scope') . $this->getStoreId()],
                ['entity_id', 'data_index']
            );
        $items = $this->getConnection()->fetchAll($select);
        $result = [];
        foreach ($items as $item) {
            $value = trim($item['data_index']);
            $id = $item['entity_id'];
            if (!isset($result[$id])) {
                $result[$id] = $value;
            } else {
                $result[$id] .= ' ' . $value;
            }
        }

        return $result;
    }
}
