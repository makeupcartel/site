<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\Component\Post\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Filter\FilterManager;

/**
 * Class Name
 * @package Aheadworks\Blog\Ui\Component\Post\Listing\Column
 */
class Name extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterManager $filterManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterManager $filterManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->filterManager = $filterManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $item['name_url'] = $this->context->getUrl('aw_blog_admin/post/edit', ['id' => $item['id']]);
        }

        return $dataSource;
    }
}
