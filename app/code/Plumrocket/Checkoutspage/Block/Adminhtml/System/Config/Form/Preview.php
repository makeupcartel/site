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

namespace Plumrocket\Checkoutspage\Block\Adminhtml\System\Config\Form;

class Preview extends \Magento\Backend\Block\Template implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'Plumrocket_Checkoutspage::system/config/preview.phtml';

    /**
     * @var \Plumrocket\Checkoutspage\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendHelper;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManager       $storeManager
     * @param \Plumrocket\Checkoutspage\Helper\Data   $dataHelper
     * @param \Magento\Sales\Model\OrderFactory       $orderFactory
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param Array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Plumrocket\Checkoutspage\Helper\Data $dataHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
        $this->_orderFactory = $orderFactory;
        $this->_backendHelper = $backendHelper;
        parent::__construct($context, $data);
    }

     /**
     * Render fieldset html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * Get url to preview page
     * @param  string $action page | email
     * @param  string $type   old | new
     * @return string         url
     */
    public function getPreviewUrl($action, $type)
    {
        $url = $this->_storeManager
                    ->getStore($this->_getStoreId())
                    ->getUrl(
                        $this->_dataHelper->getConfigSectionId() . '/preview/' . $action,
                        ['type' => $type, 'secret' =>  $this->_dataHelper->getSecretKey()]
                    );

        $url = str_replace(
            $this->_backendHelper->getAreaFrontName() . '/',
            '',
            $url
        );

        return $url;

    }

    /**
     * get last order id
     * @return int
     */
    public function getLastOrderId()
    {
        $order = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToFilter('store_id', $this->_getStoreId())
            ->setOrder('entity_id', 'DESC')
            ->setPageSize(1)
            ->getFirstItem();

        return $order->getIncrementId();
    }

    /**
     * get selected store id
     * @return int
     */
    protected function _getStoreId()
    {

        $request = $this->getRequest();

        if (!$defaultStoreId = $request->getParam('store')) {
            $websiteCode = $request->getParam('website');

            $defaultStoreId = $this->_storeManager
                ->getWebsite($websiteCode ?: null)
                ->getDefaultGroup()
                ->getDefaultStoreId();

            if (!$defaultStoreId) {
                $websites = $this->_storeManager->getWebsites(true);
                foreach ($websites as $website) {
                    $defaultStoreId = $website
                        ->getDefaultGroup()
                        ->getDefaultStoreId();

                    if ($defaultStoreId) {
                        break;
                    }
                }
            }
        }

        if (!$defaultStoreId) {
            $defaultStoreId = 1;
        }

        return $defaultStoreId;
    }
}
