<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\Indexer\Category\Fulltext\Action;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class IndexIterator implements \Iterator
{
    /**
     * @var \Amasty\Xsearch\Model\Indexer\Category\Fulltext\Action\DataProvider
     */
    private $dataProvider;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var array
     */
    private $categoriesIds;
    /**
     * @var int
     */
    private $lastCategoryId = 0;

    /**
     * @var array
     */
    private $categories = [];

    /**
     * @var null
     */
    private $current = null;

    /**
     * @var bool
     */
    private $isValid = true;

    /**
     * @var null
     */
    private $key = null;

    /**
     * @var array
     */
    private $categoryAttributes = [];

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    private $isActive;

    public function __construct(
        DataProvider $dataProvider,
        $storeId,
        $categoriesIds,
        array $fields,
        \Magento\Eav\Model\Entity\Attribute $isActive,
        Full $actionFull
    ) {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setActionFull($actionFull);
        $this->storeId = $storeId;
        $this->categoriesIds = $categoriesIds;
        $this->fields = $fields;
        $this->isActive = $isActive;
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        \next($this->categories);
        if (\key($this->categories) === null) {
            // check if storage has more items to process

            $this->categories = $this->dataProvider->getSearchableCategories(
                $this->storeId,
                $this->categoriesIds,
                $this->lastCategoryId
            );

            if (!count($this->categories)) {
                $this->isValid = false;
                return;
            }

            $categoryAttributes = [];

            foreach ($this->categories as $categoryData) {
                $this->lastCategoryId = $categoryData['entity_id'];
                $categoryAttributes[$categoryData['entity_id']] = $categoryData['entity_id'];
            }

            \reset($this->categories);

            $this->categoryAttributes = $this->dataProvider->getCategoryAttributes(
                $this->storeId,
                $categoryAttributes,
                $this->fields
            );
        }

        $categoryData = \current($this->categories);

        if (!isset($this->categoryAttributes[$categoryData['entity_id']])) {
            $this->next();
            return;
        }

        $categoryAttr = $this->categoryAttributes[$categoryData['entity_id']];

        if (!isset($categoryAttr[$this->isActive->getId()])
            || !$categoryAttr[$this->isActive->getId()]
        ) {
            $this->next();
            return;
        }

        $categoryIndex = [$categoryData['entity_id'] => $categoryAttr];

        $index = $this->dataProvider->prepareCategoryIndex(
            $categoryIndex,
            $categoryData,
            $this->storeId
        );

        $this->current = $index;
        $this->key = $categoryData['entity_id'];
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return $this->isValid;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->lastCategoryId = 0;
        $this->key = null;
        $this->current = null;
        unset($this->categories);
        $this->categories = [];
        $this->next();
    }
}
