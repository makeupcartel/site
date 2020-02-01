<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

class Brand extends AbstractSearch
{
    const BRAND_BLOCK_PAGE = 'brand';
    const SPLIT_SYMBOLS = '/( |&|:|-|\*|=)/';

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @inheritdoc
     */
    public function getBlockType()
    {
        return self::BRAND_BLOCK_PAGE;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->dataObjectFactory = $this->getData('dataObjectFactory');
    }

    /**
     * @inheritdoc
     */
    public function generateCollection()
    {
        $collection = parent::generateCollection();
        foreach ($this->getBrands() as $item) {
            if ($this->intersectSearch($item) || $this->fulltextSearch($item)) {
                $dataObject = $this->dataObjectFactory->create();
                $dataObject->setData($item);
                $collection->addItem($dataObject);
                if (count($collection) == $this->getLimit()) {
                    break;
                }
            }
        }

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function getIndexFulltextValues()
    {
        $result = [];
        foreach ($this->getBrands() as $brand) {
            $result[] = trim(trim($brand['label']) . ' ' . trim($brand['description']));
        }

        return $result;
    }

    /**
     * @param array $item
     * @return bool
     */
    private function intersectSearch(array $item)
    {
        $query = $this->explodeString($this->getQuery()->getQueryText());
        return
            empty($query)
            || array_intersect($this->explodeString($item['label']), $query)
            || array_intersect($this->explodeString($item['description']), $query);
    }

    /**
     * Simulate fulltext wildcard search.
     *
     * @param array $item
     * @return bool
     */
    private function fulltextSearch(array $item)
    {
        $result = false;
        $query = $this->getQuery()->getQueryText();
        if (strlen($query) > 2) {
            $haystack = (string)$item['label'] . (string)$item['description'];
            $result = strpos(strtolower($haystack), strtolower($query)) !== false;
        }

        return $result;
    }

    /**
     * @param $string
     * @return array
     */
    private function explodeString($string)
    {
        return preg_split(self::SPLIT_SYMBOLS, strtolower($string), -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @return array
     */
    public function getBrands()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getSearchUrl(\Magento\Framework\DataObject $item)
    {
        return $item->getUrl();
    }

    /**
     * @inheritdoc
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getLabel());
    }

    /**
     * @inheritdoc
     */
    public function getDescription(\Magento\Framework\DataObject $item)
    {
        $descStripped = $this->stripTags($item->getDescription(), null, true);
        $this->replaceVariables($descStripped);

        return $this->getHighlightText($descStripped);
    }
}
