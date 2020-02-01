<?php

namespace Convert\CustomerContent\Model;

class AllCategory implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * AllCategory constructor.
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        \Convert\CustomerContent\Model\CategoryFactory $categoryFactory
    ) {
        $this->_categoryFactory = $categoryFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    public function getAvailableStatuses()
    {
        $collection = $this->_categoryFactory->create()->getCollection();
        $type = [];
        foreach ($collection as $data) {
            $type[$data->getId()] = $data->getTitle();
        }
        return $type;
    }
}
