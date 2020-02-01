<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Block;

use \Magento\SalesRule\Model as SalesRule;

class Coupon extends Page
{

    const DEFAULT_AUTO_GENERATED_LENGTH  = 8;

    /**
     * @var SalesRule\Rule
     */
    protected $_ruleModel;

    /**
     * @var SalesRule\Rule
     */
    protected $_ruleCollectionFactory;

    /**
     * @var SalesRule\Coupon
     */
    protected $_couponModel;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var SalesRule\Coupon\Massgenerator
     */
    protected $_generator;

    /**
     * @var SalesRule\Coupon\Massgenerator
     */
    protected $_ruleRepository;

    /**
     * @var Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @param \Plumrocket\Checkoutspage\Helper\Data            $helper
     * @param SalesRule\Rule                                   $ruleModel
     * @param SalesRule\Coupon                                 $couponModel
     * @param  SalesRule\Coupon\Massgenerator                  $generator
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Sales\Model\OrderFactory                $orderFactory
     * @param \Magento\Cms\Model\Template\FilterProvider       $filterProvider
     * @param Array                                            $data
     */
    public function __construct(
        \Plumrocket\Checkoutspage\Helper\Data $helper,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        SalesRule\Rule $ruleModel,
        SalesRule\Coupon $couponModel,
        SalesRule\Coupon\Massgenerator $generator,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\SalesRule\Model\RuleRepository $ruleRepository,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($helper, $context, $data);
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
        $this->_ruleModel = $ruleModel;
        $this->_couponModel = $couponModel;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_generator = $generator;
        $this->_ruleRepository = $ruleRepository;
        $this->filterProvider = $filterProvider;

    }

    /**
     * Is section enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getSettings('coupon/enabled');
    }

    /**
     * Get coupon code
     * @return string code
     */
    public function getCoupon()
    {
        $order = $this->getOrder();

        if (!$order->getNextOrderPromoCode()) {

            $coupon = false;
            $ruleId = 0;
            $ruleIds = array();
            $orderProducts = $order->getAllItems();

            foreach ($orderProducts as $item) {
                $ruleIds[] = $item->getProduct()->getPrNextOrderCoupon();
            }

            if (!empty($ruleIds)) {
                $ruleColl = $this->_ruleCollectionFactory
                    ->create()
                    ->addFieldToFilter('coupon_type', ['in' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC,
                        \Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO])
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('rule_id', ['in' => implode(',', $ruleIds)]);

                $ruleColl->getSelect()->order('sort_order ASC')->limit('1');

                $rule = $ruleColl->getFirstItem();
                $ruleId = $rule->getId();
                if ($code = $rule->getCode()) {
                    $coupon = $code;
                } else if ($ruleId) {
                    $coupon = $this->generateCouponCode($ruleId);
                }
            }

            if (!$coupon) {
                $couponId = $this->getSettings('coupon/coupon');
                if ($couponId == \Plumrocket\Checkoutspage\Model\Config\Source\Coupon::CUSTOM_COUPON_CODE) {
                    $coupon = $this->getSettings('coupon/custom_coupon');
                } else {
                    $rule = $this->_ruleModel->load($couponId);
                    $ruleId = $rule->getId();

                    if (!$rule->getIsActive()
                            || $rule->getCouponType()  == \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON
                    ) {
                        $coupon = false;
                    } else if ($code = $rule->getCouponCode()) {
                        $coupon = $code;
                    } else {
                        $coupon = $this->generateCouponCode($ruleId);
                    }
                }
            }

            if ($coupon) {
                if ($ruleId) {
                    $order->setPrNextOrderRuleId($ruleId);
                }
                $order->setNextOrderPromoCode($coupon);
                if ($order->getPayment()) { //fix for error in \Magento\Payment\Observer\SalesOrderBeforeSaveObserver::execute()
                    $order->save();
                }
            } else {
                $order->setPrNextOrderRuleId(0);
                $order->setNextOrderPromoCode(false);
            }
        }

        return $order->getNextOrderPromoCode();
    }


    /**
     * generate coupun code
     * @return string
     */
    public function generateCouponCode($ruleId)
    {
        $data = [
            'max_probability'   => 25,
            'max_attempts'      => 10,
            'uses_per_customer' => 1,
            'uses_per_coupon'   => 1,
            'qty'               => 1, //number of coupons to generate
            'length'            => 8, //length of coupon string
            /**
             * Possible values include:
             * \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_ALPHANUMERIC
             * \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_ALPHABETICAL
             * \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_NUMERIC
             */
            'format'          => \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_ALPHANUMERIC,
            'rule_id'         => $ruleId, //the id of the rule you will use as a template
        ];

        $this->_generator->setData($data);
        $this->_generator->generatePool();
        $coupons = $this->_generator->getGeneratedCodes();

        return  $coupons[0];
    }

    /**
     * can display coupon code
     * @return boolean
     */
    public function canDisplay()
    {

        if (!$this->isEnabled() || !$this->_helper->moduleEnabled()) {
            return false;
        }

        $customerGroupId = $this->_customerSession->getCustomerGroupId();
        $configGroupIds = explode(',', $this->getSettings('coupon/customer_group'));
        if (!in_array($customerGroupId, $configGroupIds)) {
            return false;
        }

        $order = $this->getOrder();
        if (!$order) {
            return false;
        }

        if ($this->getSettings('coupon/display_once')) {

            $collection = $this->_orderFactory->create()
                ->getCollection()
                ->addFieldToFilter('next_order_promo_code', ['neq' => ''])
                ->addFieldToFilter('entity_id', ['neq' => $order->getId()]);

            if ($customerId = $order->getCustomerId()) {
                $collection->addFieldToFilter(
                    ['customer_id', 'customer_email'],
                    [
                        ['eq' => $customerId],
                        ['eq' => $order->getCustomerEmail()]
                    ]
                );
            } else {
                $collection
                    ->addFieldToFilter('customer_email', $order->getCustomerEmail())
                    ->addFieldToFilter('store_id', $order->getStoreId());
            }

            $collection->setPageSize(1);

            if ($collection->getSize()) {
                return false;
            }

        }

        return true;
    }

    /**
     * get message for coupon
     * @return string
     */
    public function getCouponMessage()
    {
        $ruleId = $this->getOrder()->getPrNextOrderRuleId();
        $ruleDescription = false;
        if ($ruleId) {
            $ruleDescription = $this->_ruleRepository->getById($ruleId)->getDescription();
        }
        if ($ruleDescription && $ruleDescription != '') {
            return $ruleDescription;
        } else {
            return $this->filterProvider->getBlockFilter()->filter($this->getSettings('coupon/message'));
        }
    }

    /**
     * is background
     * @return boolean
     */
    public function isBackground()
    {
        return !empty($this->getSettings('coupon/background'));
    }

    /**
     * get background for block
     * @return string url
     */
    public function getBackground()
    {
        if (empty($this->_data['background'])) {
            $this->_data['background'] = $this->_getBackground();
        }
        return $this->_data['background'];
    }

    /**
     * get background url
     * @return string
     */
    protected function _getBackground()
    {
        $path = $this->_helper->getConfigSectionId() . '/' . $this->getSettings('coupon/background');

        if (empty($path)) {
            return '';
        }

        $backgroundUrl = $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        return $backgroundUrl;
    }

}
