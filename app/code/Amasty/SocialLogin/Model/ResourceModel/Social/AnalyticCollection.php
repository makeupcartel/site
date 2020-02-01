<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\ResourceModel\Social;

class AnalyticCollection extends Collection
{
    public function getSocialLoginData()
    {
        $this->getSelect()
            ->joinLeft(
                ['sales_item' => $this->getTable('amasty_sociallogin_sales')],
                'sales_item.social_id = main_table.social_id',
                [
                    'items' => 'SUM(sales_item.items)',
                    'amount' => 'SUM(sales_item.amount)'
                ]
            )
            ->group('main_table.social_id');
        return $this->getItems();
    }
}
