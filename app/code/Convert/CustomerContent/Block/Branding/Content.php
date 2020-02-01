<?php

namespace Convert\CustomerContent\Block\Branding;

use Magento\Framework\View\Element\Template;

/**
 * Class Content
 *
 * @package Convert\CustomerContent\Block\Branding
 */
class Content extends Template
{
    /**
     * @var \Convert\CustomerContent\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Convert\CustomerContent\Model\CategoryStoreFactory
     */
    protected $categoryStoreFactory;

    /**
     * @var \Convert\CustomerContent\Model\ContentStoreFactory
     */
    protected $contentStoreFactory;

    /**
     * @var \Convert\CustomerContent\Model\ContentCategoryFactory
     */
    protected $contentCategoryFactory;

    /**
     * @var \Convert\CustomerContent\Model\ResourceModel\Content\CollectionFactory
     */
    protected $contentCollectionFactory;

    /**
     * Content constructor.
     * @param Template\Context $context
     * @param \Convert\CustomerContent\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Convert\CustomerContent\Model\CategoryStoreFactory $categoryStoreFactory
     * @param \Convert\CustomerContent\Model\ContentStoreFactory $contentStoreFactory
     * @param \Convert\CustomerContent\Model\ContentCategoryFactory $contentCategoryFactory
     * @param \Convert\CustomerContent\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Convert\CustomerContent\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Convert\CustomerContent\Model\CategoryStoreFactory $categoryStoreFactory,
        \Convert\CustomerContent\Model\ContentStoreFactory $contentStoreFactory,
        \Convert\CustomerContent\Model\ContentCategoryFactory $contentCategoryFactory,
        \Convert\CustomerContent\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->categoryStoreFactory = $categoryStoreFactory;
        $this->contentStoreFactory = $contentStoreFactory;
        $this->contentCategoryFactory = $contentCategoryFactory;
        $this->contentCollectionFactory = $contentCollectionFactory;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllCategoryId()
    {
        $collection = $this->collectionFactory->create()->addFieldToSelect('category_id')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('title')
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('type', 1);
        $ids = [];
        foreach ($collection as $data) {
            $stores = explode(',', $data['store_id']);
            if (in_array($this->getStoreId(), $stores)) {
                $ids[] = $data['category_id'];
            }
        }
        return $ids;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTitleCategory()
    {
        $categoryId = $this->getAllCategoryId();
        $collection = [];
        if($categoryId) {
            $collection = $this->collectionFactory->create()
                ->addFieldToSelect('category_id')
                ->addFieldToSelect('title')
                ->addFieldToFilter('category_id', array($categoryId));
        }
        return $collection;
    }

    /**
     * @param $categoryId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAllContentId($categoryId)
    {
        $collection = $this->contentCategoryFactory->create()->getCollection()->addFieldToSelect('content_id')->addFieldToSelect('store_id')
            ->addFieldToFilter('status', 1)->addFieldToFilter('category_id', $categoryId);
        $ids = [];
        foreach ($collection as $data) {
            $stores = explode(',', $data['store_id']);
            if (in_array($this->getStoreId(), $stores)) {
                $ids[] = $data['content_id'];
            }
        }

        return $ids;
    }

    /**
     * @param $categoryId
     * @return mixed
     */
    public function getContentCollection($categoryId)
    {
        $collection = $this->contentCollectionFactory->create()->addFieldToSelect('content_id')
            ->addFieldToSelect('title')->addFieldToSelect('file_path')->addFieldToSelect('thumbnail')
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('category_id', $categoryId);

        return $collection;
    }

    /**
     * @param $path
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFileUrl($path)
    {
        $file = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;
        return $file;
    }

    /**
     * @param $source
     * @return int|null
     */
    public function remote_file_exists($source)
    {
        $url_headers=get_headers($source, 1);
        if(isset($url_headers['Content-Type'])) {
            return ($url_headers['Content-Type'] == 'video/mp4') ? 0 : 1;
        }
        return null;
    }
}
