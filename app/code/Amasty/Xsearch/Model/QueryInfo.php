<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model;

use Amasty\Xsearch\Model\ResourceModel\UserSearch\Collection;

class QueryInfo extends \Magento\Framework\Model\AbstractModel
{
    const MONTHS = 12;

    const LIMIT_ROWS = 10;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @return array
     */
    public function getAnalyticsData($timePeriod, $isNeedLimit = true)
    {
        $isMonthPeriod = $timePeriod == Collection::GROUP_BY_MOUNTH;
        $this->dateFormat = $isMonthPeriod ? 'F Y' : 'd F Y';
        if ($isMonthPeriod) {
            $this->limit = self::MONTHS;
        } else {
            $this->limit = $isNeedLimit ? self::LIMIT_ROWS : null;
        }
        $analyticsConfig = [];

        $collection = $this->getQueryCollection()->create()->getPopularity($timePeriod);
        $this->addValues($collection, $analyticsConfig, 'popularity');
        $collection = $this->getQueryCollection()->create()->getUniqueSearch($timePeriod);
        $this->addValues($collection, $analyticsConfig, 'unique_query');
        $collection = $this->getQueryCollection()->create()->getUniqueUsers($timePeriod);
        $this->addValues($collection, $analyticsConfig, 'unique_user');
        $this->getProductClick($timePeriod, $analyticsConfig);

        return $isMonthPeriod ? array_reverse($analyticsConfig) : $analyticsConfig;
    }

    /**
     * @param $collection
     * @param $analyticsConfig
     * @param $field
     */
    private function addValues($collection, &$analyticsConfig, $field)
    {
        $collection->setLimit($this->limit);
        foreach ($collection as $key => $item) {
            $analyticsConfig[$key][$field] = $item->getData($field);
            if (!isset($analyticsConfig[$key]['created_at'])) {
                $analyticsConfig[$key]['created_at'] =
                    $this->getDateTime()->date($this->dateFormat, $item->getCreatedAt());
            }
        }
    }

    /**
     * @param $timePeriod
     * @param $analyticsConfig
     */
    private function getProductClick($timePeriod, &$analyticsConfig)
    {
        $productClickUsers = $this->getQueryCollection()->create()->getProductClickUsers($timePeriod);
        $productClickUsers->setLimit($this->limit);
        foreach ($productClickUsers as $user) {
            foreach ($analyticsConfig as $key => $item) {
                if ($this->getDateTime()->date($this->dateFormat, $user->getCreatedAt()) == $item['created_at']) {
                    $percentClick = round(($user->getProductClick() / $item['unique_user']) * 100, 2);
                    $analyticsConfig[$key]['product_click'] = $percentClick;
                    continue 2;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getMostWantedQueries($limit = null)
    {
        $queryCollection = $this->getQueryCollection();
        $searchQueries = $queryCollection->create()->getSearchQueries($limit);

        $result = [];
        foreach ($searchQueries as $key => $query) {
            $clickCount = $queryCollection->create()->getProductClickForQuery($query->getQueryId());
            $clicks = $clickCount
                ? round(($clickCount / $query->getUserKey()) * 100, 2)
                : 0;
            $result[$key] = $this->getDataObject()->create(
                ['data' => $query->toArray(['query_text', 'total_searches', 'user_key'])]
            )->setData('product_click', $clicks);
        }

        return $result;
    }
}
