<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model;

use Amasty\SocialLogin\Api\Data\SalesInterface;

class Sales extends \Magento\Framework\Model\AbstractModel implements SalesInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\SocialLogin\Model\ResourceModel\Sales::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(SalesInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(SalesInterface::ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSocialId()
    {
        return $this->_getData(SalesInterface::SOCIAL_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSocialId($socialId)
    {
        $this->setData(SalesInterface::SOCIAL_ID, $socialId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->_getData(SalesInterface::ITEMS);
    }

    /**
     * @inheritdoc
     */
    public function setItems($items)
    {
        $this->setData(SalesInterface::ITEMS, $items);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAmount()
    {
        return $this->_getData(SalesInterface::AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setAmount($amount)
    {
        $this->setData(SalesInterface::AMOUNT, $amount);

        return $this;
    }
}
