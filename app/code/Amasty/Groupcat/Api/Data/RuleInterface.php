<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Api\Data;

interface RuleInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const RULE_ID = 'rule_id';

    const NAME = 'name';

    const IS_ACTIVE = 'is_active';

    const CONDITIONS_SERIALIZED = 'conditions_serialized';

    const FORBIDDEN_ACTION = 'forbidden_action';

    const FORBIDDEN_PAGE_ID = 'forbidden_page_id';

    const ALLOW_DIRECT_LINKS = 'allow_direct_links';

    const HIDE_PRODUCT = 'hide_product';

    const HIDE_CATEGORY = 'hide_category';

    const HIDE_CART = 'hide_cart';

    const HIDE_WISHLIST = 'hide_wishlist';

    const HIDE_COMPARE = 'hide_compare';

    const PRICE_ACTION = 'price_action';

    const BLOCK_ID_VIEW = 'block_id_view';

    const BLOCK_ID_LIST = 'block_id_list';

    const STOCK_STATUS = 'stock_status';

    const FROM_DATE = 'from_date';

    const TO_DATE = 'to_date';

    const DATE_RANGE_ENABLED = 'date_range_enabled';

    const FROM_PRICE = 'from_price';

    const TO_PRICE = 'to_price';

    const BY_PRICE = 'by_price';

    const PRICE_RANGE_ENABLED = 'price_range_enabled';

    const CUSTOMER_GROUP_ENABLED = 'customer_group_enabled';

    const PRIORITY = 'priority';

    /**#@-*/

    /**
     * Returns rule id field
     *
     * @return int|null
     */
    public function getRuleId();

    /**
     * @param int $ruleId
     *
     * @return $this
     */
    public function setRuleId($ruleId);

    /**
     * Returns rule name
     *
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Returns rule activity flag
     *
     * @return int
     */
    public function getIsActive();

    /**
     * @param int $isActive
     *
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * @return int
     */
    public function getForbiddenAction();

    /**
     * @param int $action
     *
     * @return $this
     */
    public function setForbiddenAction($action);

    /**
     * @return int|null
     */
    public function getForbiddenPageId();

    /**
     * @param int|null $cmsPageId
     *
     * @return $this
     */
    public function setForbiddenPageId($cmsPageId);

    /**
     * @return int
     */
    public function getAllowDirectLinks();

    /**
     * @param int|bool $flag
     *
     * @return $this
     */
    public function setAllowDirectLinks($flag);

    /**
     * @return int
     */
    public function getHideProduct();

    /**
     * @param int|bool $flag
     *
     * @return $this
     */
    public function setHideProduct($flag);

    /**
     * @return int
     */
    public function getHideCategory();

    /**
     * @param int|bool $flag
     *
     * @return $this
     */
    public function setHideCategory($flag);

    /**
     * @return int
     */
    public function getHideCart();

    /**
     * @param int $option
     *
     * @return $this
     */
    public function setHideCart($option);

    /**
     * @return int
     */
    public function getHideWishlist();

    /**
     * @param int $option
     *
     * @return $this
     */
    public function setHideWishlist($option);

    /**
     * @return int
     */
    public function getHideCompare();

    /**
     * @param int $option
     *
     * @return $this
     */
    public function setHideCompare($option);

    /**
     * @return int
     */
    public function getPriceAction();

    /**
     * @param int $option
     *
     * @return $this
     */
    public function setPriceAction($option);

    /**
     * @return int|null
     */
    public function getBlockIdView();

    /**
     * @param int|null $cmsBlockId
     *
     * @return $this
     */
    public function setBlockIdView($cmsBlockId);

    /**
     * @return int|null
     */
    public function getBlockIdList();

    /**
     * @param int|null $cmsBlockId
     *
     * @return $this
     */
    public function setBlockIdList($cmsBlockId);

    /**
     * @return int
     */
    public function getStockStatus();

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStockStatus($status);

    /**
     * @return string
     */
    public function getFromDate();

    /**
     * @param string $date
     *
     * @return $this
     */
    public function setFromDate($date);

    /**
     * @return string
     */
    public function getToDate();

    /**
     * @param string $date
     *
     * @return $this
     */
    public function setToDate($date);

    /**
     * @return int
     */
    public function getDateRangeEnabled();

    /**
     * @param int|bool $flag
     *
     * @return $this
     */
    public function setDateRangeEnabled($flag);

    /**
     * @return float
     */
    public function getFromPrice();

    /**
     * @param float $price
     *
     * @return $this
     */
    public function setFromPrice($price);

    /**
     * @return float
     */
    public function getToPrice();

    /**
     * @param float $price
     *
     * @return $this
     */
    public function setToPrice($price);

    /**
     * @return int
     */
    public function getByPrice();

    /**
     * @param int $priceType
     *
     * @return $this
     */
    public function setByPrice($priceType);

    /**
     * @return int
     */
    public function getPriceRangeEnabled();

    /**
     * @param int|bool $flag
     *
     * @return $this
     */
    public function setPriceRangeEnabled($flag);

    /**
     * @return int
     */
    public function getCustomerGroupEnabled();

    /**
     * @param int|bool $flag
     *
     * @return $this
     */
    public function setCustomerGroupEnabled($flag);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority);
}
