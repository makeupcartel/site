<?php

namespace Convert\Catalog\Plugin\Product\ProductList;

/**
 * Class Toolbar
 *
 * @package Convert\Catalog\Plugin\Product\ProductList
 */
class Toolbar
{
    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $_collection;

    /*
    * Plugin
    *
    * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
    * @param \Closure $proceed
    * @param \Magento\Framework\Data\Collection $collection
    * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
    */
    public function aroundSetCollection(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
        \Closure $proceed,
        $collection
    ) {
        $this->_collection = $collection;
        $currentOrder = $toolbar->getCurrentOrder();
        $currentDirection = $toolbar->getCurrentDirection();
        $result = $proceed($collection);

        if ($currentOrder) {
            switch ($currentOrder) {

            case 'position':
                $this->_collection->addAttributeToSort(
                    $toolbar->getCurrentOrder(),
                    $toolbar->getCurrentDirection()
                );
            break;
			case 'name':
                $this->_collection
                    ->getSelect()
                    ->order('name ASC');
            break;
            case 'price':
                $this->_collection
                    ->getSelect()
                    ->order('final_price ASC');
            break;
            default:        
                $this->_collection
                    ->setOrder($currentOrder, $currentDirection);
            break;

            }
        }

        return $result;
    }
}