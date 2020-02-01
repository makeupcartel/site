<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml;

use Aheadworks\Blog\Api\Data\PostInterfaceFactory;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Aheadworks\Blog\Model\Source\Post\Status;
use Aheadworks\Blog\Model\PostFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Aheadworks\Blog\Model\Converter\Condition as ConditionConverter;
use Aheadworks\Blog\Model\Rule\ProductFactory;
use Aheadworks\Blog\Model\Serialize\SerializeInterface;
use Aheadworks\Blog\Model\Serialize\Factory as SerializeFactory;

/**
 * Class Post
 * @package Aheadworks\Blog\Controller\Adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Post extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Blog::posts';

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var PostInterfaceFactory
     */
    protected $postDataFactory;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var ConditionConverter
     */
    private $conditionConverter;

    /**
     * @var ProductFactory
     */
    private $productRuleFactory;

    /**
     * @var SerializeInterface
     */
    private $serializer;

    /**
     * @param Context $context
     * @param PostRepositoryInterface $postRepository
     * @param PostInterfaceFactory $postDataFactory
     * @param PostFactory $postFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param JsonFactory $resultJsonFactory
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param DataPersistorInterface $dataPersistor
     * @param ConditionConverter $conditionConverter
     * @param ProductFactory $productRuleFactory
     * @param SerializeFactory $serializeFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        PostRepositoryInterface $postRepository,
        PostInterfaceFactory $postDataFactory,
        PostFactory $postFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        JsonFactory $resultJsonFactory,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        ConditionConverter $conditionConverter,
        ProductFactory $productRuleFactory,
        SerializeFactory $serializeFactory
    ) {
        parent::__construct($context);
        $this->postRepository = $postRepository;
        $this->postDataFactory = $postDataFactory;
        $this->postFactory = $postFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->dataPersistor = $dataPersistor;
        $this->conditionConverter = $conditionConverter;
        $this->productRuleFactory = $productRuleFactory;
        $this->serializer = $serializeFactory->create();
    }

    /**
     * Prepare post data for save
     *
     * @param array $postData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function preparePostData(array $postData)
    {
        $postId = isset($postData['id']) && $postData['id']
            ? $postData['id']
            : false;
        if (!$postId) {
            unset($postData['id']);
        }
        if (!$postData['has_short_content']) {
            $postData['short_content'] = '';
        }
        if ($saveAction = $this->getRequest()->getParam('action')) {
            if ($saveAction == 'save_as_draft') {
                $postData['status'] = Status::DRAFT;
            }
            if ($saveAction == 'publish') {
                $postData['status'] = Status::PUBLICATION;
                $postData['publish_date'] = $this->getPreparedPublishDate($postData);
            }
            if ($saveAction == 'schedule') {
                $postData['status'] = Status::SCHEDULED;
                $postData['publish_date'] = $this->getPreparedPublishDate($postData);
            }
        }
        if ($this->storeManager->hasSingleStore()) {
            $postData['store_ids'] = [$this->storeManager->getStore(true)->getId()];
        }
        if (!isset($postData['category_ids']) || empty($postData['category_ids'])) {
            $postData['category_ids'] = [];
        }
        if (!isset($postData['tag_names'])) {
            $postData['tag_names'] = [];
        }
        $arrayForConvertation = [];
        if (isset($postData['rule']['conditions'])) {
            $conditionArray = $this->convertFlatToRecursive($postData['rule'], ['conditions']);
            if (is_array($conditionArray['conditions']['1'])) {
                $arrayForConvertation = $conditionArray['conditions']['1'];
            }
        } elseif (isset($postData['product_condition']) && !empty($postData['product_condition'])) {
            $arrayForConvertation = $this->serializer->unserialize($postData['product_condition']);
        } else {
            $productRule = $this->productRuleFactory->create();
            $arrayForConvertation = $productRule->setConditions([])->getConditions()->asArray();
        }
        $postData['product_condition'] = $this->conditionConverter
            ->arrayToDataModel($arrayForConvertation);
        if (isset($postData['featured_image_file'][0]['path'])) {
            $postData['featured_image_file'] = $postData['featured_image_file'][0]['path'];
        } else {
            $postData['featured_image_file'] = '';
        }
        if (isset($postData['use_default'])) {
            if (!empty($postData['use_default']['meta_twitter_site'])) {
                $postData['meta_twitter_site'] = '';
            }
        }
        return $postData;
    }
    /**
     * Get prepared publish date
     *
     * @param array $postData
     * @return string
     */
    private function getPreparedPublishDate(array $postData)
    {
        $publishDate = $this->dateTime->gmtDate(
            \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT
        );
        if (!empty($postData['publish_date'])) {
            $publishDateTimestamp = strtotime($postData['publish_date']);
            $publishDate = $this->dateTime->gmtDate(
                \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT,
                $publishDateTimestamp
            );
        }
        return $publishDate;
    }

    /**
     * Get conditions data recursively
     *
     * @param array $data
     * @param array $allowedKeys
     * @return array
     */
    private function convertFlatToRecursive(array $data, $allowedKeys = [])
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys) && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $result;

                    for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            }
        }
        return $result;
    }
}
