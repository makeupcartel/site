<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Ui\Component\Listing\Columns;

class SocialAccounts extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $socialAccounts = $item[$this->getData('name')];
                if ($socialAccounts) {
                    $socialAccounts = explode(',', $socialAccounts);
                    foreach ($socialAccounts as $i => $account) {
                        $socialAccounts[$i] = ucfirst($account);
                    }
                    $socialAccounts = implode(',', $socialAccounts);
                    $item[$this->getData('name')] = $socialAccounts;
                }

            }
        }
        return $dataSource;
    }
}
