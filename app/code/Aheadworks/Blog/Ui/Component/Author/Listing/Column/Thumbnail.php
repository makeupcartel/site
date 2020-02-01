<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\Component\Author\Listing\Column;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Model\Image\Info as ImageInfo;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Thumbnail
 * @package Aheadworks\Blog\Ui\Component\Author\Listing\Column
 */
class Thumbnail extends Column
{
    /**
     * @var ImageInfo
     */
    private $imageInfo;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ImageInfo $imageInfo
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ImageInfo $imageInfo,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageInfo = $imageInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[AuthorInterface::IMAGE_FILE]) {
                    $imgUrl = $this->imageInfo->getMediaUrl($item[AuthorInterface::IMAGE_FILE]);
                    $item[$fieldName . '_src'] = $item[$fieldName . '_orig_src'] = $imgUrl;
                }
            }
        }
        return $dataSource;
    }
}
