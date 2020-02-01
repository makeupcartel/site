<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

class Category extends AbstractSearch
{
    const CATEGORY_BLOCK_TYPE = 'category';

    /**
     * @var array
     */
    private $categoryData;

    /**
     * @return string
     */
    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_TYPE;
    }

    /**
     * @inheritdoc
     */
    protected function generateCollection()
    {
        $rootId = $this->_storeManager->getStore()->getRootCategoryId();
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = parent::generateCollection()
            ->setStoreId($storeId)
            ->addNameToResult()
            ->addIsActiveFilter()
            ->addAttributeToSelect('description')
            ->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%'])
            ->addSearchFilter($this->getQuery()->getQueryText())
            ->setPageSize($this->getLimit());
        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function getResults()
    {
        $result = parent::getResults();
        foreach ($this->getSearchCollection() as $index => $item) {
            $result[$index]['full_path'] = $this->renderFullCategoryPath($item);
        }

        return $result;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    function getName(\Magento\Framework\DataObject $item)
    {
        if ($this->xSearchHelper->getModuleConfig($this->getBlockType() . '/full_path')) {
            $result = $this->generateName($this->getItemTitle($item));
        } else {
            $result = parent::getName($item);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getItemTitle(\Magento\Framework\DataObject $item)
    {
        $path = array_reverse(explode(',', $item->getPathInStore()));
        $categoryTitle = '';
        $titles = $this->getCategoryData();
        foreach ($path as $id) {
            if (!empty($titles[$id])) {
                $categoryTitle .= $titles[$id]['name'];
                $categoryTitle .= ($id !== $item->getId()) ? ' > ' : '';
            }
        }

        return $categoryTitle ?: $item->getName();
    }

    /**
     * @return array
     */
    private function getCategoryData()
    {
        if ($this->categoryData === null) {
            $this->categoryData = [];
            $collection = $this->getData('categoryCollectionFactory')
                ->create()
                ->addNameToResult();
            foreach ($collection as $category) {
                $this->categoryData[$category->getId()] = [
                    'name' => $category->getName(),
                    'url' => $this->getRelativeLink($category->getUrl())
                ];
            }

        }

        return $this->categoryData;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(\Magento\Framework\DataObject $category)
    {
        $descStripped = $this->stripTags($category->getDescription(), null, true);

        return $this->getHighlightText($descStripped);
    }

    /**
     * @param $item
     * @return string
     */
    private function renderFullCategoryPath($item)
    {
        $path = array_reverse(explode(',', $item->getPathInStore()));
        $categoryTitle = '';
        $data = $this->getCategoryData();
        foreach ($path as $id) {
            if (!empty($data[$id])) {
                $categoryTitle .= sprintf(
                    '<a href="%1$s" class="am-item-link" title="%2$s">%2$s</a>',
                    $data[$id]['url'],
                    $data[$id]['name']
                );
            }
        }

        return $categoryTitle ?: $item->getName();
    }
}
