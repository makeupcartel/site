<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\System\Config\Source;

use \Magento\Framework\Option\ArrayInterface;
use \Magento\Framework\Module\Manager as ModuleManager;

class Brands implements ArrayInterface
{
    const ENABLED = '1';
    const DISABLED = '0';
    const NOT_INSTALLED = '2';

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::DISABLED => __('No'),
            self::ENABLED => __('Yes')
        ];
        if (!$this->moduleManager->isEnabled('Amasty_ShopbyBrand')) {
            $options = [
                self::NOT_INSTALLED => __('Not installed')
            ];
        }

        return $options;
    }
}
