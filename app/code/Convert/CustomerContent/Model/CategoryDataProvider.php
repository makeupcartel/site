<?php
namespace Convert\CustomerContent\Model;

use Convert\CustomerContent\Model\ResourceModel\Category\CollectionFactory;

class CategoryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * CategoryDataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $categoryCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $categoryCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $categoryCollectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        $this->_loadedData = [];
        foreach ($items as $data) {
            $this->_loadedData[$data->getId()] = $data->getData();
        }
        return $this->_loadedData;
    }
}
