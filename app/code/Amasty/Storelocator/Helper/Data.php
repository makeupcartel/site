<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var int
     */
    protected $_statusId = null;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context
    ){
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getImageUrl($name)
    {
        $path = $this->_storeManager->getStore()->getBaseUrl( \Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $path . 'amasty/amlocator/'. $name;
    }

    public function validateLocation($location, $product)
    {
        $location->setProduct($product);
        $valid = $location->getActions()->validate($location);
        if ($valid) {
            return true;
        }

        return false;
    }

    public function getDaysNames()
    {
        return [
            'monday' => __('Monday'),
            'tuesday' => __('Tuesday'),
            'wednesday' => __('Wednesday'),
            'thursday' => __('Thursday'),
            'friday' => __('Friday'),
            'saturday' => __('Saturday'),
            'sunday' => __('Sunday'),
        ];
    }

}