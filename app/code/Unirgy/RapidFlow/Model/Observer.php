<?php

namespace Unirgy\RapidFlow\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Unirgy\RapidFlow\Helper\Data as HelperData;

class Observer
{
    /**
     * @var HelperData
     */
    protected $_rapidFlowHelperData;

    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    /**
     * @var Feed
     */
    protected $_rapidFlowModelFeed;

    public function __construct(HelperData $rapidFlowHelperData,
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Feed $rapidFlowModelFeed)
    {
        $this->_rapidFlowHelperData = $rapidFlowHelperData;
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_rapidFlowModelFeed = $rapidFlowModelFeed;

    }

    public function adminhtml_version($observer)
    {
        $this->_rapidFlowHelperData->addAdminhtmlVersion('Unirgy_RapidFlow');
    }

    /**
    * Check for extension update news
    *
    * @param EventObserver $observer
    */
    public function adminhtml_controller_action_predispatch(EventObserver $observer)
    {
        if ($this->_appConfigScopeConfigInterface->getValue('urapidflow/admin/notifications')) {
            try {
                $this->_rapidFlowModelFeed->checkUpdate();
            } catch (Exception $e) {
                // silently ignore
            }
        }
    }
}
