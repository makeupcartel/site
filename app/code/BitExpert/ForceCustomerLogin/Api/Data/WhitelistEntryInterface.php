<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Api\Data;

/**
 * Interface WhitelistEntryInterface
 *
 * @package BitExpert\ForceCustomerLogin\Api\Data
 */
interface WhitelistEntryInterface
{
    /**
     * Attribute keys
     */
    const KEY_WHITELIST_ENTRY_ID = 'whitelist_entry_id';
    const KEY_STORE_ID = 'store_id';
    const KEY_LABEL = 'label';
    const KEY_URL_RULE = 'url_rule';
    const KEY_EDITABLE = 'editable';
    const KEY_STRATEGY = 'strategy';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return string|null
     */
    public function getLabel();

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return string|null
     */
    public function getUrlRule();

    /**
     * @param string $urlRule
     * @return $this
     */
    public function setUrlRule($urlRule);

    /**
     * @return boolean|null
     */
    public function getEditable();

    /**
     * @param boolean $editable
     * @return $this
     */
    public function setEditable($editable);

    /**
     * @return string|null
     */
    public function getStrategy();

    /**
     * @param string $strategy
     * @return $this
     */
    public function setStrategy($strategy);
}
