<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Index;

class Ajax extends \Magento\Framework\App\Action\Action
{
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
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    private $dataHelper;

    /**
     * Ajax constructor.
     *
     * @param \Magento\Framework\App\Action\Context     $context
     * @param \Magento\Framework\Json\EncoderInterface  $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $this->_objectManager = $context->getObjectManager();
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Amasty\Storelocator\Model\ResourceModel\Location\Collection $locationCollection */
        $locationCollection = $this->_objectManager->get('Amasty\Storelocator\Model\Location')->getCollection();
        $productId = $this->getRequest()->getParam('product');
        $categoryId = $this->getRequest()->getParam('category');
        $storeListId = $this->getRequest()->getParam('storeListId');
        $mapId = $this->getRequest()->getParam('mapId');
        $locationCollection->applyDefaultFilters($productId, $categoryId);
        $params = $this->getRequest()->getParams();
        $locationCollection->applyAttributeFilters($params);

        $this->_view->loadLayout();

        $arrayCollection = [];

        foreach ($locationCollection->getLocationData() as $item) {
            $arrayCollection['items'][] = $item;
        }

        $arrayCollection['totalRecords'] = isset($arrayCollection['items']) ? count($arrayCollection['items']) : 0;

        $left = $this->_view->getLayout()
            ->createBlock(\Amasty\Storelocator\Block\Location::class)
            ->setTemplate('Amasty_Storelocator::left.phtml')
            ->setMapId($mapId)
            ->setAmlocatorStoreList($storeListId)
            ->toHtml();

        $res = array_merge_recursive(
            $arrayCollection,
            ['block' => $left, 'storeListId' => $storeListId]
        );

        $json = $this->_jsonEncoder->encode($res);

        $this->getResponse()->setBody($json);
    }
}
