<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Api\Data;

interface RequestInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const REQUEST_ID = 'request_id';

    const NAME = 'name';

    const EMAIL = 'email';

    const PHONE = 'phone';

    const PRODUCT_ID = 'product_id';

    const STORE_ID = 'store_id';

    const COMMENT = 'comment';

    const CREATED_AT = 'created_at';

    const STATUS = 'status';

    const MESSAGE_TEXT = 'message_text';

    /**
     * Returns request id field
     *
     * @return int|null
     */
    public function getRequestId();

    /**
     * @param int $requestId
     *
     * @return $this
     */
    public function setRequestId($requestId);

    /**
     * Returns name
     *
     * @return string|null
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Returns email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email);

    /**
     * Returns phone
     *
     * @return string|null
     */
    public function getPhone();

    /**
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone($phone);

    /**
     * Returns comment
     *
     * @return string|null
     */
    public function getComment();

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment);

    /**
     * Returns product id
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int $productId
     *
     * @return $this
     */
    public function setProductId($productId);
    /**
     * Returns store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Returns created at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Returns created at
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status);

    /**
     * Returns admin answer message text
     *
     * @return string|null
     */
    public function getMessageText();

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setMessageText($text);
}
