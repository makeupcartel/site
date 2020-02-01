<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\DataProvider;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Model\ResourceModel\Category\Grid\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Category data provider
 */
class CategoryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];
        $dataFromForm = $this->dataPersistor->get('aw_blog_category');
        if (!empty($dataFromForm)) {
            $object = $this->collection->getNewEmptyItem();
            $object->setData($dataFromForm);
            $data[$object->getId()] = $object->getData();
            $this->dataPersistor->clear('aw_blog_category');
        } else {
            $id = $this->request->getParam($this->getRequestFieldName());
            /** @var \Aheadworks\Blog\Model\Category $category */
            foreach ($this->getCollection()->getItems() as $category) {
                if ($id == $category->getId()) {
                    $data[$id] = $category->getData();
                }
            }
            if ($parentId = $this->request->getParam('parent', 0)) {
                $data[$id] = [CategoryInterface::PARENT_ID => $parentId];
            }
        }
        return $data;
    }
}
