<?php

namespace Convert\Catalog\Plugin\Model;

/**
 * Class Config
 *
 * @package Convert\Catalog\Plugin\Model
 */
class Config
{
    /**
     * Adding custom options and changing labels
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param [] $options
     * @return []
     */
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        $options['position'] = __('Sort by');        
        $options['name'] = __('Name');
        return $options;
    }
}