<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\Indexer;

use Magento\Framework\App\Area;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;

class UrlFactory extends \Magento\Framework\UrlFactory
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $appState,
        $instanceName = UrlInterface::class
    ) {
        parent::__construct($objectManager, $instanceName);
        $this->appState = $appState;
    }

    /**
     * @inheritdoc
     */
    public function create(array $data = [])
    {
        if ($this->appState->isAreaCodeEmulated() && $this->appState->getAreaCode() === Area::AREA_FRONTEND) {
            $nameOrig = $this->_instanceName;
            $this->_instanceName = \Magento\Framework\Url::class;
            $result = parent::create($data);
            $this->_instanceName = $nameOrig;
        } else {
            $result = parent::create($data);
        }

        return $result;
    }
}
