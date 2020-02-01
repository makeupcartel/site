<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\CatalogSearch\Model\Search;

use Magento\CatalogSearch\Model\Search\RequestGenerator as SearchRequestGenerator;

class RequestGenerator
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(\Magento\Framework\App\ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param SearchRequestGenerator $subject
     * @param array $requests
     * @return array
     */
    public function afterGenerate(SearchRequestGenerator $subject, $requests)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '<')) {
            foreach ($requests as $requestContainer => &$request) {
                if (isset($request['queries'][$requestContainer]['queryReference'])
                    && !empty($request['queries'][$requestContainer]['queryReference'])
                ){
                    foreach ($request['queries'][$requestContainer]['queryReference'] as &$reference) {
                        $reference['clause'] = 'must';
                    }
                }
            }
        }
        return $requests;
    }

}
