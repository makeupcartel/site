<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_FacebookPixel
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FacebookPixel\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\FacebookPixel\Model\Session
     */
    protected $fbPixelSession;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\FacebookPixel\Model\Session $fbPixelSession
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\FacebookPixel\Model\Session $fbPixelSession,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->scopeConfig          = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->fbPixelSession = $fbPixelSession;
        $this->taxConfig = $taxConfig;

        parent::__construct($context);
    }

    /**
     * @param array $data
     * @return false|string
     */
    public function serializes($data)
    {
        $result = json_encode($data);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $result;
    }

    /**
     * @return \Magento\Tax\Model\Config
     */
    public function isTaxConfig()
    {
        return $this->taxConfig;
    }

    /**
     * @return array
     */
    public function listPageDisable()
    {
        $list = $this->getConfig(
            'bss_facebook_pixel/event_tracking/disable_code'
        );
        if ($list) {
            return explode(',', $list);
        } else {
            return [];
        }
    }

    /**
     * Based on provided configuration path returns configuration value.
     *
     * @param string $configPath
     * @param string|int $scope
     * @return string
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Add slashes to string and prepares string for javascript.
     *
     * @param string $str
     * @return string
     */
    public function escapeSingleQuotes($str)
    {
        return str_replace("'", "\'", $str);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @return \Bss\FacebookPixel\Model\Session
     */
    public function getSession()
    {
        return $this->fbPixelSession;
    }

    /**
     * @param array $data
     * @return string
     */
    public function getPixelHtml($data = false)
    {
        $json = 404;
        if ($data) {
            $json =$this->serializes($data);
        }

        return $json;
    }
}
