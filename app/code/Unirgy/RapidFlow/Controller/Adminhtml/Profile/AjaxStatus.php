<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Data as HelperData;
use Magento\Framework\View\LayoutFactory;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\Profile as ProfileResource;
use Zend\Json\Json;

class AjaxStatus extends AbstractProfile
{
    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    public function __construct(Context $context,
                                Profile $profile,
                                HelperData $catalogHelper,
                                ProfileResource $profileResource,
                                LayoutFactory $layoutFactory)
    {
        $this->_layoutFactory = $layoutFactory;

        parent::__construct($context, $profile, $catalogHelper, $profileResource);
    }

    public function execute()
    {
        $profile = $this->_getProfile();

        $result = [
            'run_status' => $profile->getRunStatus(),
            'html' => $this->_layoutFactory->create()
                ->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Status')
                ->setProfile($profile)
                ->toHtml()
        ];

        $this->getResponse()->representJson(Json::encode($result));
    }
}
