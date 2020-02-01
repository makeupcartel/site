<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Repository;

use BitExpert\ForceCustomerLogin\Api\Data\Collection\WhitelistEntryCollectionFactoryInterface;
use BitExpert\ForceCustomerLogin\Api\Data\WhitelistEntryFactoryInterface;
use BitExpert\ForceCustomerLogin\Model\WhitelistEntrySearchResultInterfaceFactory as SearchResultFactory;
use Magento\Store\Model\StoreManager;

/**
 * Class WhitelistRepository
 *
 * @package BitExpert\ForceCustomerLogin\Model
 */
class WhitelistRepository implements \BitExpert\ForceCustomerLogin\Api\Repository\WhitelistRepositoryInterface
{
    /**
     * @var WhitelistEntryFactoryInterface
     */
    private $entityFactory;
    /**
     * @var WhitelistEntryCollectionFactoryInterface
     */
    private $collectionFactory;
    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * WhitelistRepository constructor.
     *
     * @param WhitelistEntryFactoryInterface $entityFactory
     * @param WhitelistEntryCollectionFactoryInterface $collectionFactory
     * @param StoreManager $storeManager
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        WhitelistEntryFactoryInterface $entityFactory,
        WhitelistEntryCollectionFactoryInterface $collectionFactory,
        StoreManager $storeManager,
        SearchResultFactory $searchResultFactory
    ) {
        $this->entityFactory = $entityFactory;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createEntry($entityId, $label, $urlRule, $strategy = self::DEFAULT_STRATEGY, $storeId = 0)
    {
        $whitelist = $this->entityFactory->create();

        if (null !== $entityId) {
            $whitelist = $whitelist->load($entityId);
        }

        if (!$whitelist->getId()) {
            $whitelist = $this->entityFactory->create()->load($label, 'label');
        }

        // check if existing whitelist entry is editable
        if ($whitelist->getId() &&
            !$whitelist->getEditable()) {
            throw new \RuntimeException(
                'Whitelist entry not editable.'
            );
        }

        $whitelist->setLabel($label);
        $whitelist->setUrlRule($urlRule);
        $whitelist->setStrategy($strategy);
        $whitelist->setStoreId($storeId);
        $whitelist->setEditable(true);

        $validator = new \BitExpert\ForceCustomerLogin\Validator\WhitelistEntry();
        $validator->validate($whitelist);

        $whitelist->save();

        return $whitelist;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteEntry($id)
    {
        $whitelist = $this->entityFactory->create()->load($id);
        if (!$whitelist->getId() ||
            !$whitelist->getEditable()) {
            return false;
        }

        $whitelist->delete();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getCollection()
    {
        $currentStore = $this->storeManager->getStore();

        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            'store_id',
            [
                'in' => [
                    static::ROOT_STORE_ID,
                    (int) $currentStore->getId()
                ]
            ]
        );

        return $collection->load();
    }

    /**
     * {@inheritDoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        /** @var \BitExpert\ForceCustomerLogin\Api\Data\Collection\WhitelistEntrySearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $searchResult->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResult->setCurPage($searchCriteria->getCurrentPage());
        $searchResult->setPageSize($searchCriteria->getPageSize());

        return $searchResult;
    }
}
