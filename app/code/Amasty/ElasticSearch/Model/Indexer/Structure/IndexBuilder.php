<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Structure;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\IndexBuilderInterface;

class IndexBuilder implements IndexBuilderInterface
{
    /**
     * @var \Amasty\ElasticSearch\Api\Data\Indexer\Structure\AnalyzerBuilderInterface
     */
    private $analyserBuilder;

    /**
     * @var int
     */
    private $storeId = 0;

    /**
     * @var \Amasty\ElasticSearch\Model\Debug
     */
    private $debug;

    public function __construct(
        \Amasty\ElasticSearch\Api\Data\Indexer\Structure\AnalyzerBuilderInterface $analyserBuilder,
        \Amasty\ElasticSearch\Model\Debug $debug
    ) {
        $this->analyserBuilder = $analyserBuilder;
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $settings = [
            'index.mapping.total_fields.limit' => IndexBuilderInterface::MAX_FIELDS_COUNT,
            'max_result_window'                => IndexBuilderInterface::MAX_RESULT_COUNT,
            'analysis' => $this->analyserBuilder->build($this->storeId)
        ];
        /*
         * debug code for settings
        \Amasty\Base\Debug\VarDump::setArrayDepthLevel(5);
        $this->debug->debug($settings);
        */

        return $settings;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }
}
