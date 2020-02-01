<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Rule;

class CmsBlockOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array|null
     */
    protected $options;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->collectionFactory->create()->toOptionArray();
        }
        return $this->options;
    }
}
