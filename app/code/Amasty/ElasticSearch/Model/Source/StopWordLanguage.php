<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Source;

class StopWordLanguage implements \Magento\Framework\Option\ArrayInterface
{
    const ANY = '0';
    const ALL = '1';

    private $laguages = [
        '_arabic_' => 'Arabic (General)',
        '_armenian_' => 'English (Unated States)',
        '_basque_' => 'Basque (Spain)',
        '_bengali_' => 'Bengali (Bangladesh)',
        '_brazilian_' => 'Portuguese (Brasil)',
        '_bulgarian_' => 'Bulgarian',
        '_catalan_' => 'Catalan (Spain)',
        '_czech_' => 'Czech',
        '_danish_' => 'Danish',
        '_dutch_' => 'Dutch (Gemeral)',
        '_english_' => 'English (United Kingdom)',
        '_finnish_' => 'Finnish',
        '_french_' => 'French (General)',
        '_galician_' => 'Galician (Spain)',
        '_german_' => 'German',
        '_greek_' => 'Greek',
        '_hindi_' => 'Hindi',
        '_hungarian_' => 'Hungarian',
        '_indonesian_' => 'Indonesian',
        '_irish_' => 'English (Ireland)',
        '_italian_' => 'Italian',
        '_latvian_' => 'Latvian',
        '_norwegian_' => 'Norwegian (General)',
        '_persian_' => 'Persian',
        '_portuguese_' => 'Portuguese (General)',
        '_romanian_' => 'Romanian',
        '_russian_' => 'Russian',
        '_sorani_' => 'Sorani',
        '_spanish_' => 'Spanish (General)',
        '_swedish_' => 'Swedish',
        '_thai_' => 'Thai',
        '_turkish_' => 'Turkish'
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->laguages as $key => $label) {
            $options[] = [
                'value' => $key,
                'label' => __($label),
            ];
        }

        return $options;
    }
}
