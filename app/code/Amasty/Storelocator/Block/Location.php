<?php

namespace Amasty\Storelocator\Block;

use Magento\Framework\View\Element\Template;

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */
class Location extends Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'Amasty_Storelocator::center.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * IO File
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_ioFile;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection
     */
    protected $attributeCollection;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $timezoneInterface;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var \Amasty\Storelocator\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * Location constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context              $context
     * @param \Magento\Framework\ObjectManagerInterface                     $objectManager
     * @param \Magento\Framework\Registry                                   $coreRegistry
     * @param \Magento\Framework\Json\EncoderInterface                      $jsonEncoder
     * @param \Magento\Framework\Filesystem\Io\File                         $ioFile
     * @param \Amasty\Storelocator\Helper\Data                              $dataHelper
     * @param \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollectionFactory
     * @param \Amasty\Base\Model\Serializer                                 $serializer
     * @param \Amasty\Storelocator\Model\ConfigProvider                     $configProvider
     * @param array                                                         $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Storelocator\Model\ConfigProvider $configProvider,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_filesystem = $context->getFilesystem();
        $this->_jsonEncoder = $jsonEncoder;
        $this->_ioFile = $ioFile;
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->attributeCollection = $attributeCollection;
        $this->timezoneInterface = $context->getLocaleDate();
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
    }

    /**
     * @return bool
     */
    public function isWidget()
    {
        return $this->getNameInLayout() != 'amasty.locator.center'
            && $this->getNameInLayout() != 'amasty.locator.left';
    }

    /**
     * @return string
     */
    public function getLeftBlockHtml()
    {
        $html = $this->setTemplate('Amasty_Storelocator::left.phtml')->toHtml();

        return $html;
    }

    /**
     * @return string
     */
    public function getMapStyles()
    {
        $styles = sprintf(
            'width: %s; height: %s;',
            $this->escapeHtml($this->getMapWidth()),
            $this->escapeHtml($this->getMapHeight())
        );

        return $styles;
    }

    /**
     * @return string
     */
    public function getStoreListStyles()
    {
        $styles = sprintf(
            'width: %s; height: %s;',
            $this->escapeHtml($this->getStoreListWidth()),
            $this->escapeHtml($this->getStoreListHeight())
        );

        return $styles;
    }

    /**
     * @return string
     */
    public function getMainBlockStyles()
    {
        $styles = '';
        if (!$this->isWrap()) {
            $styles = 'clear:both;';
        }

        return $styles;
    }

    /**
     * Get setting for showing store list in widget
     *
     * @return string
     */
    public function getShowLocations()
    {
        if (!$this->hasData('show_locations')) {
            return true; // not widget
        }

        return $this->getData('show_locations');
    }

    /**
     * Get setting for showing search block in widget
     *
     * @return string
     */
    public function getShowSearch()
    {
        if (!$this->hasData('show_search')) {
            return true; // not widget
        }

        return $this->getData('show_search');
    }

    /**
     * Get map width style
     *
     * @return string
     */
    public function getMapWidth()
    {
        if (!$this->hasData('map_width')) {
            $this->setData('map_width', $this->configProvider->getMapWidth());
        }

        return $this->getData('map_width');
    }

    /**
     * Get map height style
     *
     * @return string
     */
    public function getMapHeight()
    {
        if (!$this->hasData('map_height')) {
            $this->setData('map_height', $this->configProvider->getMapHeight());
        }

        return $this->getData('map_height');
    }

    /**
     * Get store list height style
     *
     * @return string
     */
    public function getStoreListHeight()
    {
        if (!$this->hasData('store_list_height')) {
            $this->setData('store_list_height', $this->configProvider->getStoreListHeight());
        }

        return $this->getData('store_list_height');
    }

    /**
     * Get store list width style
     *
     * @return string
     */
    public function getStoreListWidth()
    {
        if (!$this->hasData('store_list_width')) {
            $this->setData('store_list_width', $this->configProvider->getStoreListWidth());
        }

        return $this->getData('store_list_width');
    }

    /**
     * Get map wrap style
     *
     * @return bool
     */
    public function isWrap()
    {
        return (bool)$this->getData('wrap_block');
    }

    /**
     * Return map Element unic ID
     *
     * @return string
     */
    public function getMapId()
    {
        if (!$this->hasData('map_id')) {
            $this->setData('map_id', $this->escapeHtml(uniqid('amlocator-map-canvas')));
        }

        return $this->getData('map_id');
    }

    /**
     * Return search Element unic ID
     *
     * @return string
     */
    public function getSearchId()
    {
        if (!$this->hasData('search_id')) {
            $this->setData('search_id', $this->escapeHtml(uniqid('amlocator-search')));
        }

        return $this->getData('search_id');
    }

    /**
     * Return search radius Element unic ID
     *
     * @return string
     */
    public function getSearchRadiusId()
    {
        if (!$this->hasData('radius_id')) {
            $this->setData('radius_id', $this->escapeHtml(uniqid('amlocator-radius')));
        }

        return $this->getData('radius_id');
    }

    /**
     * Return Filter by attribute Element unic ID
     *
     * @return string
     */
    public function getAttributeFilterId()
    {
        if (!$this->hasData('filter_attribute_id')) {
            $this->setData('filter_attribute_id', $this->escapeHtml(uniqid('amasty-filter-attribute-id')));
        }

        return $this->getData('filter_attribute_id');
    }

    /**
     * Return Nearby button Element unic ID
     *
     * @return string
     */
    public function getNearbyButtonId()
    {
        if (!$this->hasData('nearby_button_id')) {
            $this->setData('nearby_button_id', $this->escapeHtml(uniqid('locateNearBy')));
        }

        return $this->getData('nearby_button_id');
    }

    /**
     * Return am_lat input unic ID
     *
     * @return string
     */
    public function getAmLatId()
    {
        if (!$this->hasData('am_lat')) {
            $this->setData('am_lat', $this->escapeHtml(uniqid('am_lat')));
        }

        return $this->getData('am_lat');
    }

    /**
     * Return am_lng input unic ID
     *
     * @return string
     */
    public function getAmLngId()
    {
        if (!$this->hasData('am_lng')) {
            $this->setData('am_lng', $this->escapeHtml(uniqid('am_lng')));
        }

        return $this->getData('am_lng');
    }

    /**
     * Get store list html
     *
     * @param \Amasty\Storelocator\Model\Location $location
     * @return string
     */
    public function getStoreListHtml($location)
    {
        $storeListTemplate = $this->getStoreListTemplate();
        $storeListTemplate = preg_replace_callback(
            '/{{if(.*)}}(.*){{\/\if(.*)}}/miU',
            function ($match) use ($location) {
                if (isset($location[$match['1']])) {
                    return str_replace('{{' . $match['1'] . '}}', $location[$match['1']], $match['2']);
                } else {
                    return '';
                }
            },
            $storeListTemplate
        );

        return preg_replace_callback(
            '/{{(.*)}}/miU',
            function ($match) use ($location) {
                if (isset($location[$match['1']])) {
                    if ($match['1'] == 'name') {
                        return '<div class="location_header">' . $location[$match['1']] . '</div>';
                    }

                    return $location[$match['1']];
                } else {
                    return '';
                }
            },
            $storeListTemplate
        );
    }

    /**
     * Return search button Element unic ID
     *
     * @return string
     */
    public function getSearchButtonId()
    {
        if (!$this->hasData('search_button_id')) {
            $this->setData('search_button_id', $this->escapeHtml(uniqid('sortByFilter')));
        }

        return $this->getData('search_button_id');
    }

    /**
     * Return stores list Element unic ID
     *
     * @return string
     */
    public function getStoresListId()
    {
        if (!$this->hasData('amlocator_store_list')) {
            $this->setData('amlocator_store_list', $this->escapeHtml(uniqid('amlocator_store_list')));
        }

        return $this->getData('amlocator_store_list');
    }

    /**
     * Get text for link in widget
     *
     * @return string
     */
    public function getWidgetLinkText()
    {
        return $this->getData('widget_link_text');
    }

    public function getLocationCollection()
    {
        $categoryId = $this->getRequest()->getParam('category');
        $productId = $this->getRequest()->getParam('product');
        if (!$productId && is_object($this->getProduct())) {
            $productId = $this->getProduct()->getId();
        }

        if (is_object($this->getCategory())) {
            $categoryId = $this->getCategory()->getId();
        }
        /** \Amasty\Storelocator\Model\Location $locationCollection */
        $locationCollection = $this->_objectManager->get('Amasty\Storelocator\Model\Location')->getCollection();
        $locationCollection->applyDefaultFilters($productId, $categoryId);
        $params = $this->getRequest()->getParams();
        $locationCollection->applyAttributeFilters($params);

        return $locationCollection;
    }

    public function validateLocations($locationCollection, $product)
    {
        foreach ($locationCollection as $location) {
            $valid = $this->dataHelper->validateLocation($location, $product);
            if ($valid) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get store list template
     *
     * @return string
     */
    public function getStoreListTemplate()
    {
        $storeListTamplate = $this->_scopeConfig->getValue(
            'amlocator/locator/store_list_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $storeListTamplate;
    }

    public function getBaloonTemplate()
    {
        $baloon = $this->_scopeConfig->getValue(
            'amlocator/locator/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $store_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $store_url =  $store_url . 'amasty/amlocator/';

        $baloon = str_replace(
            '{{photo}}',
            '<img src="' . $store_url . '{{photo}}">',
            $baloon
        );

        $attributeTemplate = $this->_scopeConfig->getValue(
            'amlocator/locator/attribute_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $this->_jsonEncoder->encode(["baloon" => $baloon, "attributeTemplate" => $attributeTemplate]);
    }

    public function getAmStoreMediaUrl()
    {
        $store_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $store_url =  $store_url . 'amasty/amlocator/';

        return $store_url;
    }

    /**
     * Get use browser location
     *
     * @return bool
     */
    public function getUseBrowserLocation()
    {
        $useBrowser = $this->_scopeConfig->getValue(
            'amlocator/geoip/usebrowserip',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return (bool)$useBrowser;
    }

    /**
     * Get use geo ip
     *
     * @return bool
     */
    public function getGeoUse()
    {
        $geoUse = $this->_scopeConfig->getValue(
            'amlocator/geoip/use',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return (bool)$geoUse;
    }

    public function getConvertTime()
    {
        $convertTime = $this->_scopeConfig->getValue(
            'amlocator/locator/convert_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $convertTime;
    }

    /**
     * Get clustering for map
     *
     * @return bool
     */
    public function getClustering()
    {
        $enableClustering = $this->_scopeConfig->getValue(
            'amlocator/locator/clustering',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return (bool)$enableClustering;
    }

    /**
     * Get counting distance
     *
     * @return bool
     */
    public function getCountingDistance()
    {
        $enableCounting = $this->_scopeConfig->getValue(
            'amlocator/locator/count_distance',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return (bool)$enableCounting;
    }

    /**
     * Get allowed countries
     *
     * @return string
     */
    public function getAllowedCountries()
    {
        $countriesString = $this->_scopeConfig->getValue(
            'amlocator/locator/allowed_countries',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!empty($countriesString)) {
            $countriesArray = explode(',', $countriesString);
        } else {
            $countriesArray = array();
        }

        return $this->_jsonEncoder->encode($countriesArray);
    }

    /**
     * Getting time for location
     *
     * @param string $from
     * @param string $to
     *
     * @return string $scheduleToday
     */
    public function getWorkingTime($from, $to)
    {
        $scheduleToday = $from . ' - ' . $to;
        if ($scheduleToday == '00:00 - 00:00') {
            return $this->getTextForClosedShop();
        }
        $needConvertTime = $this->getConvertTime();
        if ($needConvertTime) {
            $scheduleToday = date("g:i a", strtotime($from)) . ' - ' . date("g:i a", strtotime($to));
        }

        return $scheduleToday;
    }

    public function getJsonLocations()
    {
        $locations = $this->getLocations();
        $locationArray = [];
        $locationArray['items'] = [];
        foreach ($locations as $location) {
            //$location->setData('schedule', $this->_jsonEncoder->encode($this->serializer->unserialize($location->getData('schedule'))));
            $locationArray['items'][] = $location;
        }
        $locationArray['totalRecords'] = count($locationArray['items']);
        $store = $this->_storeManager->getStore(true)->getId();
        $locationArray['currentStoreId'] = $store;

        return $this->_jsonEncoder->encode($locationArray);
    }

    public function getLocations()
    {
        $locations = $this->getLocationCollection();
        /** @var \Amasty\Storelocator\Model\ResourceModel\Location\Collection $locations */

        return $locations->getLocationData();
    }

    /**
     * Get text for closed shop
     *
     * @return string
     */
    public function getTextForClosedShop()
    {
        return $this->_scopeConfig->getValue(
            'amlocator/locator/close_text',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get zoom for map
     *
     * @return int
     */
    public function getMapZoom()
    {
        $mapZoom = $this->_scopeConfig->getValue(
            'amlocator/locator/zoom',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return (int)$mapZoom;
    }

    /**
     * Get automatic locate nearest location
     *
     * @return bool
     */
    public function getAutomaticLocate()
    {
        $automaticLocate = $this->_scopeConfig->getValue(
            'amlocator/geoip/automatic_locate',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return (bool)$automaticLocate;
    }

    public function getApiKey($isClean = false)
    {
        $apiKey = $this->_scopeConfig->getValue(
            'amlocator/locator/api',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$isClean) {
            if ($apiKey != "") {
                $apiKey = "&key=" . $apiKey;
            } else {
                $apiKey = "";
            }
        }
        return $apiKey;
    }

    public function getDistanceConfig()
    {
        $distance = $this->_scopeConfig->getValue(
            'amlocator/locator/distance',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $distance;
    }

    public function getShowAttributes()
    {
        $showAttributes = $this->_scopeConfig->getValue(
            'amlocator/locator/show_attrs',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $showAttributes;
    }

    /**
     * Get radius from config
     *
     * @return array
     */
    public function getSearchRadius()
    {
        $searchRadius = $this->_scopeConfig->getValue(
            'amlocator/locator/radius',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return explode(',', $searchRadius);
    }

    public function getLinkToMap($productId = 0)
    {
        if (!$productId) {
            return $this->getUrl(
                $this->_scopeConfig->getValue(
                    'amlocator/general/url',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }
        $link  = $this->getUrl(
            $this->_scopeConfig->getValue(
                'amlocator/general/url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            ['_query' => ["product" => $productId]]
        );

        return $link;
    }

    public function getQueryString()
    {
        if ($this->getRequest()->getParam('product') !== null) {
            return '?' . http_build_query($this->getRequest()->getParams());
        }
        return '';
    }

    public function getProduct()
    {
        if ($this->_coreRegistry->registry('current_product')) {
            return $this->_coreRegistry->registry('current_product');
        }

        return false;
    }

    /**
     * Get current category
     *
     * @return false|\Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        if ($this->_coreRegistry->registry('current_category')) {
            return $this->_coreRegistry->registry('current_category');
        }

        return false;
    }

    public function getProductId()
    {
        if ($this->getRequest()->getParam('product')) {
            return (int)$this->getRequest()->getParam('product');
        }
        if ($this->_coreRegistry->registry('current_product')) {
            return $this->_coreRegistry->registry('current_product')->getId();
        }

        return false;
    }

    /**
     * Get current category
     *
     * @return false|\Magento\Catalog\Model\Category
     */
    public function getCategoryId()
    {
        if ($this->_coreRegistry->registry('current_category')) {
            return $this->_coreRegistry->registry('current_category')->getId();
        }

        return false;
    }

    public function getProductById($productId)
    {
        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);

        return $product;
    }

    public function getLinkText()
    {
        $linkText = $this->_scopeConfig->getValue(
            'amlocator/locator/linktext',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $linkText;
    }

    public function getTarget()
    {
        $target = '';

        $newPage = $this->_scopeConfig->getValue(
            'amlocator/locator/new_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($newPage) {
            $target = 'target="_blank"';
        }
        return $target;
    }

    public function getAttributes()
    {
        $collection = $this->attributeCollection
            ->joinAttributes();
        $attrAsArray = $collection->getAttributes();

        $storeId = $this->_storeManager->getStore(true)->getId();

        $attributes = [];

        foreach ($attrAsArray as $attribute) {
            $attributeId = $attribute['attribute_id'];
            if (!array_key_exists($attributeId, $attributes)) {
                $attrLabel = $attribute['frontend_label'];
                $labels = $this->serializer->unserialize($attribute['label_serialized']);
                if (isset($labels[$storeId]) && $labels[$storeId]) {
                    $attrLabel = $labels[$storeId];
                }
                $attributes[$attributeId] = [
                    'attribute_id' => $attributeId,
                    'label' => $attrLabel,
                    'options' => [],
                    'frontend_input' => $attribute['frontend_input']
                ];
            }

            if ($attribute['frontend_input'] == 'boolean') {
                $attributes[$attributeId]['options'][0] = __('No');
                $attributes[$attributeId]['options'][1] = __('Yes');
            } else {
                $options = $this->serializer->unserialize($attribute['options_serialized']);
                $optionLabel = $options[0];
                if (isset($options[$storeId]) && $options[$storeId]) {
                    $optionLabel = $options[$storeId];
                }
                $attributes[$attributeId]['options'][$attribute['value_id']] = $optionLabel;
            }
        }

        return $attributes;
    }

    /**
     * Add metadata to page header
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->getNameInLayout() && strpos($this->getNameInLayout(), 'link') === false) {
            if ($title = $this->configProvider->getMetaTitle()) {
                $this->pageConfig->getTitle()->set($title);
            }

            if ($description = $this->configProvider->getMetaDescription()) {
                $this->pageConfig->setDescription($description);
            }
        }

        return parent::_prepareLayout();
    }
}
