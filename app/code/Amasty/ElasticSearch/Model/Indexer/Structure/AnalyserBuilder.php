<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Structure;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\AnalyzerBuilderInterface;
use Amasty\ElasticSearch\Model\ResourceModel\StopWord\CollectionFactory as StopWordCollectionFactory;
use Amasty\ElasticSearch\Model\ResourceModel\Synonym\CollectionFactory as SynoymCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AnalyserBuilder
 * @package Amasty\ElasticSearch\Model\Indexer\Structure
 */
class AnalyserBuilder implements AnalyzerBuilderInterface
{
    /**
     * @var StopWordCollectionFactory
     */
    private $stopWordCollectionFactory;

    /**
     * @var SynoymCollectionFactory
     */
    private $synoymCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        StopWordCollectionFactory $stopWordCollectionFactory,
        SynoymCollectionFactory $synoymCollectionFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->stopWordCollectionFactory = $stopWordCollectionFactory;
        $this->synoymCollectionFactory = $synoymCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build($storeId)
    {
        $analyser = [
            'analyzer' => [
                //"the A*b-1^2 O'Neil's" => ["ab12", "oneil"]
                'default' => [
                    'type'      => 'custom',
                    'tokenizer' => 'whitespace',
                    'filter'    => [
                        'lowercase',
                        'stop_filter',
                        "synonym",
                        'word_delimiter'
                    ],
                ],
                //"the A*b-1^2 O'Neil's" => ["a*b-1^2", "o'neil's"]
                'allow_spec_chars' => [
                    'type'      => 'custom',
                    'tokenizer' => 'whitespace',
                    'filter'    => [
                        'lowercase',
                        'stop_filter',
                        'synonym'
                    ],
                ],
            ],
            'filter'   => [
                'word_delimiter' => [
                    // https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-word-delimiter-tokenfilter.html
                    'type'                    => 'word_delimiter',
                    'catenate_all'            => true,
                    'catenate_words'          => false,
                    'catenate_numbers'        => false,
                    //^ catenate all
                    'generate_word_parts'     => false,
                    'generate_number_parts'   => false,
                    'split_on_case_change'    => false,
                    'preserve_original'       => false,
                    'split_on_numerics'       => false,
                    'stem_english_possessive' => true,
                ],
                'stop_filter' => [
                    "type" => "stop",
                    "stopwords" => $this->getStopWords($storeId)
                ],
                "synonym" => [
                    "type" => "synonym",
                    "lenient" => true,
                    "synonyms" => $this->getSynonyms($storeId)
                ]
            ],
        ];

        return $analyser;
    }

    /**
     * @param $storeId
     * @return array|string
     */
    private function getStopWords($storeId)
    {
        $usePredefined = $this->scopeConfig->isSetFlag('amasty_elastic/index/use_language_stopwords');
        if ($usePredefined) {
            return $this->scopeConfig->getValue('amasty_elastic/index/stopwords_language');
        } else {
            $stopWords = [];
            $collection = $this->stopWordCollectionFactory->create();
            $collection->addStoreFilter($storeId);
            foreach ($collection as $stopWord) {
                $stopWords[] = $stopWord->getTerm();
            }
            if (!count($stopWords)) {
                $stopWords = '_none_';
            }
        }

        return $stopWords;
    }

    /**
     * @param $storeId
     * @return array
     */
    private function getSynonyms($storeId)
    {
        $synonyms = [];
        $collection = $this->synoymCollectionFactory->create();
        $collection->addStoreFilter($storeId);
        foreach ($collection as $synonym) {
            $synonyms[] = $synonym->getTerm();
        }

        return $synonyms ?: ['']; //can't pass empty array to elastic 5.x
    }
}
