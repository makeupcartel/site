<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */

namespace Amasty\Geoip\Controller\Adminhtml;

use Amasty\Geoip\Helper\Data as Helper;
use Amasty\Geoip\Model\Import;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonData;

abstract class GeoipAbstract extends Action
{

    /**
     * @var Import
     */
    protected $importModel;

    /**
     * @var Helper
     */
    protected $geoipHelper;

    /**
     * @var JsonData
     */
    protected $jsonHelper;

    public function __construct(
        Context $context,
        Import $importModel,
        Helper $geoipHelper,
        JsonData $jsonHelper
    ) {
        parent::__construct($context);
        $this->importModel = $importModel;
        $this->geoipHelper = $geoipHelper;
        $this->jsonHelper = $jsonHelper;
    }

}
