<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Xsearch\Block;

use Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface;
use Amasty\Xsearch\Block\Search\AbstractSearch;
use Amasty\Xsearch\Block\Search\Product;
use Amasty\Xsearch\Model\Indexer\ElasticExternalProvider;
use Amasty\ElasticSearch\Model\Search\GetRequestQuery;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\AdapterFactory;
use Amasty\ElasticSearch\Model\Search\Adapter;

class Search
{
    const AMASTY_XSEARCH_GENERAL_ENABLE_POPUP_INDEX = 'amasty_xsearch/general/enable_popup_index';

    /**
     * @var array
     */
    private $results = [];

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @var GetRequestQuery
     */
    private $getRequestQuery;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $indexedTypes;

    /**
     * @var \Magento\Framework\Search\Request\BuilderFactory
     */
    private $requestBuilderFactory;

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    public function __construct(
        AdapterFactory $adapterFactory,
        ClientRepositoryInterface $clientRepository,
        GetRequestQuery $getRequestQuery,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Search\Request\BuilderFactory $requestBuilderFactory,
        \Amasty\ElasticSearch\Model\Config $config,
        array $indexedTypes = []
    ) {
        $this->adapterFactory = $adapterFactory;
        $this->clientRepository = $clientRepository;
        $this->getRequestQuery = $getRequestQuery;
        $this->storeManager = $storeManager;
        $this->indexedTypes = $indexedTypes;
        $this->requestBuilderFactory = $requestBuilderFactory;
        $this->config = $config;
    }

    /**
     * @param AbstractSearch $subject
     * @param \Closure $proceed
     * @return array[]
     */
    public function aroundGetResults(
        $subject,
        \Closure $proceed
    ) {
        $results = $this->getResultsFromIndex($subject);
        if ($results === false) {
            $results = $proceed();
        }

        return $results;
    }

    /**
     * @param $block
     * @return array|array[]|bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getResultsFromIndex($block)
    {
        $results = false;
        $isIndexEnable = $this->config->getGeneralConfig(
            self::AMASTY_XSEARCH_GENERAL_ENABLE_POPUP_INDEX,
            $this->storeManager->getStore()->getId()
        ) || $block instanceof \Amasty\Xsearch\Block\Search\Category;
        if ($this->adapterFactory->create() instanceof \Amasty\ElasticSearch\Model\Search\Adapter && $isIndexEnable) {
            $type = $block->getBlockType();
            if (in_array($type, $this->indexedTypes) && $block->getQuery()) {
                $query = $block->getQuery()->getQueryText();
                if ($type === Product::BLOCK_TYPE) {
                    $results = $this->getProductIndex($query, $block->getLimit());
                    $block->setNumResults($results[Adapter::HITS]);
                    $results = $results[Adapter::PRODUCTS];
                } else {
                    $results = $this->getIndexedItems($query, $type);
                }

                $results = $this->prepareResponse($results, $block);
            }
        }

        return $results;
    }

    /**
     * @param $searchQuery
     * @param int $limit
     * @return array
     */
    private function getProductIndex($searchQuery, $limit)
    {
        $requestBuilder = $this->requestBuilderFactory->create();
        $scope = $this->storeManager->getStore()->getId();
        $requestBuilder->bindDimension('scope', $scope);
        $requestBuilder->setRequestName('quick_search_container');
        $requestBuilder->bind('visibility', [3, 4]);
        $requestBuilder->bind('search_term', $searchQuery);
        $requestBuilder->setSize($limit);
        $request = $requestBuilder->create();
        $searchResponse = $this->adapterFactory->create()->queryAdvancedSearchProduct($request);
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        foreach ($searchResponse[Adapter::PRODUCTS] as &$product) {
            if (!empty($product['img'])) {
                $product['img'] = str_replace(Product::MEDIA_URL_PLACEHOLDER, $mediaUrl, $product['img']);
            }
        }

        return $searchResponse;
    }

    /**
     * @param $searchQuery
     * @param $indexType
     * @return mixed
     * @throws \Elasticsearch\Common\Exceptions\Missing404Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getIndexedItems($searchQuery, $indexType)
    {
        if (!isset($this->results[$searchQuery])) {
            $queryArray = array_map(function ($item) {
                return mb_strlen($item) > 2 ? $item . '*' : $item;
            }, array_filter(explode(' ', $searchQuery)));
            $elasticQuery = implode(' OR ', $queryArray);
            foreach ($this->indexedTypes as $label) {
                $this->results[$searchQuery][$label] = [];
            }
            $elasticQuery = preg_replace('/[^*a-zA-Z0-0\s]/', '\$1', $elasticQuery);
            $query = $this->getRequestQuery->executeExternalByFulltext(
                $elasticQuery,
                $this->storeManager->getStore()->getId(),
                ElasticExternalProvider::FULLTEXT_INDEX_FIELD,
                \Amasty\Xsearch\Controller\RegistryConstants::INDEX_ENTITY_TYPE
            );
            $elasticResponse = $this->clientRepository->get()->search($query);
            $documents = [];
            if (isset($elasticResponse['hits']['hits'])) {
                $documents = array_map(function ($item) {
                    return $item['_source'];
                }, $elasticResponse['hits']['hits']);
            }

            foreach ($documents as $document) {
                $type = $document[ElasticExternalProvider::BLOCK_TYPE_FIELD];
                unset(
                    $document[ElasticExternalProvider::BLOCK_TYPE_FIELD],
                    $document[ElasticExternalProvider::FULLTEXT_INDEX_FIELD]
                );

                $this->results[$searchQuery][$type][] = $document;
            }
        }

        return $this->results[$searchQuery][$indexType];
    }

    /**
     * @param array[] $response
     * @param AbstractSearch|Product $block
     */
    private function prepareResponse(array $response, $block)
    {
        if ($block->getLimit()) {
            $response = array_slice($response, 0, $block->getLimit());
        }

        if ($block instanceof Product) {
            $response = $block->sortProducts($response);
        }

        foreach ($response as &$item) {
            if (isset($item['name'])) {
                $item['name'] = $block->highlight($item['name']);
            }

            if (isset($item['title'])) {
                $item['title'] = $block->highlight($item['title']);
            }

            if (isset($item['description'])) {
                $item['description'] = $block->highlight($item['description']);
            }
        }

        return $response;
    }

}
