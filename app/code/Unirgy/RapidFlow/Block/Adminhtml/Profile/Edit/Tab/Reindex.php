<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Registry;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Source;

class Reindex extends Template
{
    /**
     * @var array
     */
    protected $_cacheFields;

    /**
     * @var array
     */
    protected $_reindexFields;

    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

    /**
     * @var Source
     */
    protected $_rapidFlowSource;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Indexer
     */
    protected $_indexerModel;

    /**
     * @var CacheInterface
     */
    protected $_frameworkAppCache;

    /**
     * @var TypeListInterface
     */
    protected $_cacheList;

    /**
     * @var CollectionFactory
     */
    protected $_indexerFactory;

    /**
     * @var IndexerInterface[]
     */
    protected $_indexers;

    public function __construct(
        Context $context,
        CollectionFactory $indexCollectionFactory,
        TypeListInterface $cacheList,
        Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_cacheList = $cacheList;
        $this->_indexerFactory = $indexCollectionFactory;

        parent::__construct($context, $data);
    }

    public function getReindexColumnsFields()
    {
        if (null == $this->_reindexFields) {
            $reindexFields = [];
            /** @var Indexer $indexer */
            foreach ($this->getAllIndexers() as $indexer) {
                $code = $indexer->getId();
                $reindexFields[] = array(
                    'label' => __($indexer->getTitle()),
                    'value' => $code,
                );
            }

            $reindexFields[] = array(
                'label' => __('Catalog Rules'),
                'value' => 'catalog_rules',
            );
            $this->_reindexFields = $reindexFields;
        }

        return $this->_reindexFields;
    }

    /**
     * Returns all indexers
     *
     * @return IndexerInterface[]
     */
    protected function getAllIndexers()
    {
        if (null == $this->_indexers) {
            $this->_indexers = $this->_indexerFactory->create()->getItems();
        }

        return $this->_indexers;
    }

    public function getRefreshColumnsFields()
    {
        if (null == $this->_cacheFields) {
            foreach ($this->_cacheList->getTypes() as $type) {
                $this->_cacheFields[] = [
                    'label' => __($type['cache_type']),
                    'value' => $type['id'],
                ];
            }
        }
        return $this->_cacheFields;
    }

    public function getReindexColumns()
    {
        $profile = $this->_registry->registry('profile_data');
        return array_flip((array)$profile->getData('options/reindex'));
    }

    public function getRefreshColumns()
    {
        $profile = $this->_registry->registry('profile_data');
        return array_flip((array)$profile->getData('options/refresh'));
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Unirgy_RapidFlow::urapidflow/reindex.phtml');
    }
}
