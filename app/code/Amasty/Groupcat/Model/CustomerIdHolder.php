<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model;

/**
 * Singleton for fix get customer id from session with cache fix
 * @see \Magento\Customer\Model\Layout\DepersonalizePlugin::afterGenerateXml
 * @since 1.4.3
 */
class CustomerIdHolder
{
    private $customerId;
    private $isSetUsed = false;

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = (int)$customerId;
        $this->isSetUsed = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIdInitialized()
    {
        return $this->isSetUsed;
    }
}
