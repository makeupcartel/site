<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Data as HelperData;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\LayoutFactory;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\Profile as ProfileResource;

class Chooser extends AbstractProfile
{
    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;


    public function __construct(Context $context,
                                Profile $profile,
                                HelperData $catalogHelper,
                                ProfileResource $resource,
                                LayoutFactory $layoutFactory
    )
    {
        $this->_layoutFactory = $layoutFactory;

        parent::__construct($context, $profile, $catalogHelper, $resource);
    }

    public function execute()
    {
        $this->getRequest()->setParam('form', '');
        switch ($this->getRequest()->getParam('attribute')) {
            case 'sku':
                $type = 'Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku'; // not sure if correct block!?
                break;

            case 'categories':
                $type = 'Magento\Widget\Block\Adminhtml\Widget\Catalog\Category\Chooser';
                break;
        }
        if (!empty($type)) {
            $block = $this->_layoutFactory->create()->createBlock($type);
            if ($block) {
                /** @var \Magento\Framework\Controller\Result\Raw $result */
                $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
                $result->setContents($block->toHtml());
                return $result;
            }
        }
        return null;
    }
}
