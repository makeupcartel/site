<?php

namespace Unirgy\RapidFlow\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\RapidFlow\Helper\Data as HelperData;

class AdminhtmlVersion extends AbstractObserver implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

    public function __construct(HelperData $rapidFlowHelper)
    {
        $this->_rapidFlowHelper = $rapidFlowHelper;

    }

    public function execute(Observer $observer)
    {
        $this->_rapidFlowHelper->addAdminhtmlVersion('Unirgy_RapidFlow');
    }
}
