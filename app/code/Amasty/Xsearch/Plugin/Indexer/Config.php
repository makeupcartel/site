<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Plugin\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext;

class Config
{
    const AMASTY_XSEARCH_CATEGORY_FULLTEXT = "amasty_xsearch_category_fulltext";

    /**
     * @param \Magento\Indexer\Model\Config $subject
     * @param $result
     * @return array
     */
    public function afterGetIndexers(\Magento\Indexer\Model\Config $subject, $result)
    {
        $indexers = [];
        foreach ($result as $key => $item) {
            if ($key == Fulltext::INDEXER_ID) {
                $indexers[self::AMASTY_XSEARCH_CATEGORY_FULLTEXT] = $result[self::AMASTY_XSEARCH_CATEGORY_FULLTEXT];
                unset($result[self::AMASTY_XSEARCH_CATEGORY_FULLTEXT]);
            }

            $indexers[$key] = $item;
        }

        return $indexers;
    }
}
