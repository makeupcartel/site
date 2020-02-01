<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Api\Data;

interface SocialInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const SOCIAL_ID = 'social_id';
    const CUSTOMER_ID = 'customer_id';
    const TYPE = 'type';
    const NAME = 'name';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\SocialLogin\Api\Data\SocialInterface
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getSocialId();

    /**
     * @param string|null $socialId
     *
     * @return \Amasty\SocialLogin\Api\Data\SocialInterface
     */
    public function setSocialId($socialId);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param int|null $customerId
     *
     * @return \Amasty\SocialLogin\Api\Data\SocialInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @param string|null $type
     *
     * @return \Amasty\SocialLogin\Api\Data\SocialInterface
     */
    public function setType($type);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\SocialLogin\Api\Data\SocialInterface
     */
    public function setName($name);
}
