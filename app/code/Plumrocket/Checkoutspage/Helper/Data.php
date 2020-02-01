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
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Helper;

class Data extends Main
{

    /**
     * Preview key for push data to frontend session from backend
     */
    const PREVIEW_PARAM_NAME    = 'checkoutspage_preview';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var  \Magento\Framework\App\DeploymentConfig\Reader
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $config;

    /**
     * @var AddressRenderer
     */
    protected $addressRenderer;

    /**
     * @var string
     * needed for Plumrocket Base and for function "getConfigPath"
     */
    protected $_configSectionId = 'prcheckoutspage';

    /**
     * @var string
     */
    public static $configSectionId = 'prcheckoutspage';

    /**
     * @var string
     */
    public static $routeName = 'prcheckoutspage';

    /**
     * @var string
     */
    public static $configEnablePath = 'prcheckoutspage/general/enabled';

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry     $registry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\DeploymentConfig\Reader $deploymentConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\DeploymentConfig\Reader $deploymentConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Config\Model\Config $config
    ) {
        $this->checkoutSession      = $checkoutSession;
        $this->coreRegistry         = $registry;
        $this->deploymentConfig     = $deploymentConfig;
        $this->resourceConnection   = $resourceConnection;
        $this->addressRenderer      = $addressRenderer;
        $this->config               = $config;
        parent::__construct(
            $objectManager,
            $context
        );
    }

    /**
     * is module enabled
     * @param  int $store store id
     * @return boolean
     */
    public function moduleEnabled($store = null)
    {
        return (bool)$this->getConfig(self::$configEnablePath);
    }

    /**
     * get crypt key
     * @return string
     */
    public function getCryptKey()
    {
        $env = $this->deploymentConfig->load(\Magento\Framework\Config\File\ConfigFilePool::APP_ENV);
        return (isset($env['crypt']['key'])) ? $env['crypt']['key'] : null;
    }

    /**
     * get secter ket
     * @param  int $id
     * @param  datetime $date
     * @return string
     */
    public function getSecretKey($id = null, $date = null)
    {
        return sha1($this->getCustomerKey() . $id . ($id ? ($date ? $date : date('Y-m-d')) : '')
            . ((string)$this->getCryptKey())
        );
    }

    /**
     * get order
     * @return \Magento\Sales\Model\Order order
     */
    public function getOrder()
    {
        if (!$this->coreRegistry->registry('current_order')) {
            $this->coreRegistry->register('current_order', $this->checkoutSession->getLastRealOrder());
        }
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Disable extension
     * @return void
     */
    public function disableExtension()
    {
        $connection = $this->resourceConnection->getConnection('core_write');
        $connection->delete($this->resourceConnection->getTableName('core_config_data'),
            [$connection->quoteInto('path = ?', self::$configEnablePath)]
        );

        $this->config->setDataByPath(self::$configEnablePath, 0);
        $this->config->save();
    }

    /**
     * user registry key
     * @return $this
     */
    public function unsetOrderRegistry()
    {
        if ($this->coreRegistry->registry('current_order')) {
            $this->coreRegistry->unregister('current_order');
        }
        return $this;
    }

    /**
     * Compile response data
     *
     * @param string $error
     * @return array
     */
    public function getResponseData($error = '')
    {
        if (empty($error)) {
            $response = [
                'success' => true,
            ];
        } else {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }
        return $response;
    }

    /**
     * Get merchant id
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->getConfig($this->_configSectionId . '/google_review/merchant_id');
    }

    public function getGoogleReviewAffiliateTrackingCode()
    {
        return (string)$this->getConfig($this->_configSectionId.'/google_review/affiliate_tracking_code');
    }

    public function getEstimatedDeliveryDays()
    {
        return (int)$this->getConfig($this->_configSectionId.'/google_review/estimated_delivery_days');
    }

    public function isEnabledGoogleReview()
    {
        return (bool)$this->getConfig($this->_configSectionId.'/google_review/enable');
    }

    public function getShippingAddressHtml($address)
    {
        return $this->addressRenderer->format($address, 'html') . $this->getAdditionalAddresslHtml($address);
    }

    private function getAdditionalAddresslHtml($address)
    {
        return '<br>' . $address->getEmail() . '<br>';
    }
}
