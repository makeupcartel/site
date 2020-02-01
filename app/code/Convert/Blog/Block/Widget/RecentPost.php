<?php

namespace Convert\Blog\Block\Widget;

use Aheadworks\Blog\Model\Post\FeaturedImageInfo;
use Aheadworks\Blog\Block\Post\ListingFactory;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Blog\Model\Serialize\Factory as SerializeFactory;
use Aheadworks\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\UrlInterface;

/**
 * Class RecentPost
 *
 * @package Convert\Blog\Block\Widget
 */
class RecentPost extends \Aheadworks\Blog\Block\Widget\RecentPost
{
    /**
     * @var CollectionFactory
     */
	protected $_collectionFactory;

    /**
     * RecentPost constructor.
     *
     * @param Context $context
     * @param PostRepositoryInterface $postRepository
     * @param ListingFactory $postListingFactory
     * @param Config $config
     * @param Url $url
     * @param SerializeFactory $serializeFactory
     * @param FeaturedImageInfo $imageInfo
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
	public function __construct(
        Context $context,
        PostRepositoryInterface $postRepository,
        ListingFactory $postListingFactory,
        Config $config,
        Url $url,
        SerializeFactory $serializeFactory,
        FeaturedImageInfo $imageInfo,
		CollectionFactory $collectionFactory,
		array $data = []
	)
	{
		parent::__construct(
		    $context,
            $postRepository,
            $postListingFactory,
            $config,
            $url,
            $serializeFactory,
            $imageInfo,
            $data
        );
		$this->_collectionFactory = $collectionFactory;
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
