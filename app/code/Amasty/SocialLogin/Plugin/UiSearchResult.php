<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Framework\DB\Select;

class UiSearchResult
{
    /**
     * @param SearchResult $subject
     */
    public function beforeLoad(SearchResult $subject)
    {
        if (strpos($subject->getMainTable(), 'customer_grid_flat') !== false) {
            $this->injectSelect($subject);
            return;
        }
    }

    /**
     * @param SearchResult $subject
     * @param $field
     * @param $condition
     */
    public function beforeAddFieldToFilter(SearchResult $subject, $field, $condition = null)
    {
        if (strpos($subject->getMainTable(), 'customer_grid_flat') !== false
            && $field !== 'social_accounts'
            && strpos($field, 'main_table.') === false
        ) {
            $field = 'main_table.'  . $field;
        }
        return [$field, $condition];
    }

    /**
     * @param SearchResult $subject
     */
    public function beforeGetSelectCountSql(SearchResult $subject)
    {
        if (strpos($subject->getMainTable(), 'customer_grid_flat') !== false) {
            $this->injectSelect($subject);
        }
    }

    /**
     * @param SearchResult $subject
     */
    private function injectSelect(SearchResult $subject)
    {
        $select = $subject->getSelect();
        if (strpos((string)$select, 'amasty_sociallogin_customers') === false) {
            $select->joinLeft(
                ['sociallogin' => $subject->getTable('amasty_sociallogin_customers')],
                'sociallogin.customer_id=main_table.entity_id',
                [
                    'social_accounts' => 'GROUP_CONCAT(sociallogin.type)'
                ]
            )
                ->group('main_table.entity_id');
        }

        $where = $select->getPart(Select::WHERE);
        foreach ($where as &$part) {
            if (strpos($part, '`social_accounts`') !== false) {
                $part = str_replace("`social_accounts`", 'sociallogin.type', $part);
            }
        }
        $select->setPart(Select::WHERE, $where);
    }
}
