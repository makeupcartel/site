<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Model;

use Magento\Framework\Exception\LocalizedException;

class OrderAttributesChecker
{
    const AMASTY_ORDER_ATTRIBUTES_MODULE_NAME = 'Amasty_Orderattr';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(\Magento\Framework\Module\Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isModuleExist()
    {
        if (!$this->moduleManager->isEnabled(self::AMASTY_ORDER_ATTRIBUTES_MODULE_NAME)) {
            throw new LocalizedException(__('%1 module is not exist.', self::AMASTY_ORDER_ATTRIBUTES_MODULE_NAME));
        }

        return true;
    }
}
