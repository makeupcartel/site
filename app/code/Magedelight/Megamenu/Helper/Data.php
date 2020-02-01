<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Megamenu\Helper;

use \Magento\Customer\Model\GroupFactory;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    public $customerGroupFactory;
    
    public function __construct(
        Context $context,
        GroupFactory $customerGroupFactory
    ) {
        parent::__construct($context);
        $this->customerGroupFactory = $customerGroupFactory;
    }
    
    public function getCustomerGroupsOptions()
    {
        $groupCollection = $this->customerGroupFactory->create()->getCollection()
            ->load()
            ->toOptionHash();
        $optionString = '';
        foreach ($groupCollection as $groupId => $code) {
            $optionString .= '<option value="'.$groupId.'">'.$code.'</option>';
        }

        return $optionString;
    }
    
    public function getCustomerGroups()
    {
        $groupCollection = $this->customerGroupFactory->create()->getCollection()
            ->load()
            ->toOptionHash();
        return $groupCollection;
    }

    public function isEnabled()
    {
        $currentUrl = $this->_storeManager->getStore()->getBaseUrl();
        $domain = $this->getDomainName($currentUrl);
        $selectedWebsites = $this->getConfig('magedelight/general/select_website');
        $websites = explode(',', $selectedWebsites);
        if (in_array($domain, $websites) && $this->getConfig('magedelight/general/megamenu_status') && $this->getConfig('magedelight/license/data')) {
            return true;
        } else {
            return false;
        }
    }

    public function getDomainName($domain)
    {
        $string = '';
        
        $withTrim = str_replace(["www.","http://","https://"], '', $domain);
        
        /* finding the first position of the slash  */
        $string = $withTrim;
        
        $slashPos = strpos($withTrim, "/", 0);
        
        if ($slashPos != false) {
            $parts = explode("/", $withTrim);
            $string = $parts[0];
        }
        return $string;
    }

    public function getWebsites()
    {
        $websites = $this->_storeManager->getWebsites();
        $websiteUrls = [];
        foreach ($websites as $website) {
            foreach ($website->getStores() as $store) {
                $wedsiteId = $website->getId();
                $storeObj = $this->_storeManager->getStore($store);
                $storeId = $storeObj->getId();
                $url = $storeObj->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
                $parsedUrl = parse_url($url);
                $websiteUrls[] = str_replace(['www.', 'http://', 'https://'], '', $parsedUrl['host']);
            }
        }

        return $websiteUrls;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function menuTypes()
    {
        return [
          'megamenu'=>'Mega Menu Block',
          'category'=>'Category Selection',
          'pages'=>'Page Selection',
          'link'=>'External Links'
        ];
    }
    
    public function getMenuName($key)
    {
        $menuTypes = $this->menuTypes();
        return $menuTypes[$key];
    }
}
