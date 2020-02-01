<?php
namespace Convert\Quickorder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_sessionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $_layerResolver;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryCollection;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    ) {
        parent::__construct($context);
        $this->_sessionFactory = $sessionFactory;
        $this->_request = $request;
        $this->_layerResolver = $layerResolver;
        $this->_categoryCollection = $categoryCollection;
    }

    public function isLoggedIn()
    {
        return $this->_sessionFactory->create()->isLoggedIn();
    }

    public function isQuickOrder()
    {
        $route      = $this->_request->getRouteName();
        $action     = $this->_request->getActionName();
        if ($this->isLoggedIn() && $route == 'cvquickorder' && $action == 'index') {
            return true;
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryIdIsQuickorder(){
        $collection = $this->_categoryCollection->create()->addAttributeToFilter('is_quickorder',1);
        $ids = [];
        if($collection->getSize()) {
            foreach ($collection as $cate) {
                $ids[] = $cate->getId();
            }
            return $ids;
        }
        return $ids;
    }
}
