<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Rule;

class CmsPageOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array|null
     */
    protected $options;
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            /** @var \Magento\Cms\Model\ResourceModel\Page\Collection $collection */
            $collection    = $this->collectionFactory->create();
            foreach ($collection as $item) {
                $this->options[] = ['value' => $item->getData('page_id'), 'label' => $item->getData('title')];
            }
        }
        return $this->options;
    }
}
