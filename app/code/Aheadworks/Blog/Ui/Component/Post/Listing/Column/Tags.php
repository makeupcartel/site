<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\Component\Post\Listing\Column;

use Aheadworks\Blog\Api\Data\PostInterface;

/**
 * Class Tags
 * @package Aheadworks\Blog\Ui\Component\Post\Listing\Column
 */
class Tags extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $post) {
                if (is_array($post[PostInterface::TAG_NAMES])) {
                    $post['tags'] = implode(', ', $post[PostInterface::TAG_NAMES]);
                }
            }
        }
        return $dataSource;
    }
}
