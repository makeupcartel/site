<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Api\Data;

interface SalesInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const SOCIAL_ID = 'social_id';
    const ITEMS = 'items';
    const AMOUNT = 'amount';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     */
    public function setEntityId($entityId);

    /**
     * @return string|null
     */
    public function getSocialId();

    /**
     * @param string|null $socialId
     *
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     */
    public function setSocialId($socialId);

    /**
     * @return int
     */
    public function getItems();

    /**
     * @param int $items
     *
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     */
    public function setItems($items);

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @param float $amount
     *
     * @return \Amasty\SocialLogin\Api\Data\SalesInterface
     */
    public function setAmount($amount);
}
