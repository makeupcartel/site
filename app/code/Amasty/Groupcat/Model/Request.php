<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Model;

class Request extends \Magento\Framework\Model\AbstractModel implements \Amasty\Groupcat\Api\Data\RequestInterface
{
    /**
     * Model Init
     *
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Groupcat\Model\ResourceModel\Request');
        $this->setIdFieldName('request_id');
    }

    /**#@+
     * Standard Getter and Setters start
     */
    /**
     * {@inheritdoc}
     */
    public function getRequestId()
    {
        return $this->getData(self::REQUEST_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestId($requestId)
    {
        return $this->setData(self::REQUEST_ID, $requestId);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getPhone()
    {
        return $this->getData(self::PHONE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($flag)
    {
        return $this->setData(self::CREATED_AT, $flag);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageText()
    {
        return $this->getData(self::MESSAGE_TEXT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessageText($text)
    {
        return $this->setData(self::MESSAGE_TEXT, $text);
    }
    /**#@-
     * Standard Getter and Setters finish
     */
}
