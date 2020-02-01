<?php

namespace Convert\CustomerContent\Model;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;

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
        return [self::STATUS_DISABLE => __('Disable'), self::STATUS_ENABLE => __('Enable')];
    }
}
