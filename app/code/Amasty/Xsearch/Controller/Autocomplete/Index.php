<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Controller\Autocomplete;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Amasty\Xsearch\Controller\RegistryConstants;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    private $urlDecoder;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    private $searchHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Xsearch\Helper\Data $helper,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\CatalogSearch\Helper\Data $searchHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->urlDecoder = $urlDecoder;
        $this->urlHelper = $urlHelper;
        $this->queryFactory = $queryFactory;
        $this->storeManager = $storeManager;
        $this->layerResolver = $layerResolver;
        $this->coreRegistry = $coreRegistry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->searchHelper = $searchHelper;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);

        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        $query->setStoreId($this->storeManager->getStore()->getId());
        $query = $this->helper->setStrippedQueryText($query); //replace only for mysql (for a search by products)

        $this->coreRegistry->register(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY, $query);
        if ($query->getQueryText() != '') {
            if ($this->searchHelper->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
            }
        }

        $layout = $this->layoutFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $beforeUrl = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED);

        $blocks = $this->helper->getBlocksHtml($layout);
        foreach ($blocks as $key => $data) {
            if (is_array($data) && $beforeUrl && array_key_exists('html', $data)) {
                /**
                 * by xss protection
                 */
                $beforeUrl = $this->urlDecoder->decode($beforeUrl);
                $beforeUrl = $this->urlHelper->getEncodedUrl($beforeUrl);
                $blocks[$key]['html'] = str_replace($this->urlHelper->getEncodedUrl(), $beforeUrl, $data['html']);
            }
        }

        return $resultJson->setData($blocks);
    }
}
