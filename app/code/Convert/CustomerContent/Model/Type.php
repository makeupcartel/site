<?php

namespace Convert\CustomerContent\Model;

class Type implements \Magento\Framework\Data\OptionSourceInterface
{
    const TYPE_1 = 1;
    const TYPE_2 = 2;

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
        return [self::TYPE_1 => __('Media & Branding'), self::TYPE_2 => __('Training & Video Content')];
    }
}
