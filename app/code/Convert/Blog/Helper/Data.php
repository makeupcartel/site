<?php

namespace Convert\Blog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Model\Source\Category\Status as CategoryStatus;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Blog\Model\ResourceModel\Post\CollectionFactory;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\UrlInterface;

/**
 * Class Data
 *
 * @package Convert\Blog\Helper
 */
class Data extends AbstractHelper
{

    /**
     * @var StoreManagerInterface
     */
	protected $_storeManager;

    /**
     * @var SearchCriteriaBuilder
     */
	protected $_searchCriteriaBuilder;

    /**
     * @var CategoryRepositoryInterface
     */
	protected $_categoryRepository;

    /**
     * @var CollectionFactory
     */
	protected $_collectionFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
	public function __construct(
		Context $context,
		StoreManagerInterface $storeManager,
		CollectionFactory $collectionFactory,
		CategoryRepositoryInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
	) {
		parent::__construct($context);
		$this->_storeManager = $storeManager;
		$this->_collectionFactory = $collectionFactory;
		$this->_categoryRepository = $categoryRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
	}

    /**
     * @return CategoryInterface[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        $this->_searchCriteriaBuilder
            ->addFilter(CategoryInterface::STATUS, CategoryStatus::ENABLED)
            ->addFilter(CategoryInterface::STORE_IDS, $this->_storeManager->getStore()->getId())
            ->addSortOrder(
                new SortOrder(
                    [
                        SortOrder::FIELD => CategoryInterface::SORT_ORDER,
                        SortOrder::DIRECTION => SortOrder::SORT_ASC
                    ]
                )
            );
        return $this->_categoryRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSkinBlog()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        /** @var \Aheadworks\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('store_id', ['in' => [$storeId,0]])
            ->addFieldToFilter('status', ['eq' => 'publication'])
            ->setPageSize(3)
            ->setCurPage(1)
            ->setOrder('id', 'desc');
        $listSkinBlog = $collection->getData();
        return $listSkinBlog;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseURLMedia()
    {
        $media = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $media;
    }
}
