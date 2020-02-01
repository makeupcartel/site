<?php

namespace Unirgy\RapidFlowPro\Model;

use \Unirgy\RapidFlow\Helper\Data as HelperData;

class Observer
{
    /**
     * @var HelperData
     */
    protected $_rapidFlowHelperData;

    public function __construct(HelperData $rapidFlowHelperData)
    {
        $this->_rapidFlowHelperData = $rapidFlowHelperData;

    }

    public function adminhtml_version($observer)
    {
        $this->_rapidFlowHelperData->addAdminhtmlVersion('Unirgy\RapidFlowPro');
    }
}