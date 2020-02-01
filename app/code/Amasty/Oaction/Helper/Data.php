<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */

namespace Amasty\Oaction\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->date = $date;
    }

    /**
     * Get module setting value.
     *
     * @param string $path
     * @param int    $store
     *
     * @return string
     */
    public function getModuleConfig($path, $store = 0)
    {
        return $this->scopeConfig->getValue(
            'amasty_oaction/' . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getDate()
    {
        return $this->date->gmtDate();
    }
}
