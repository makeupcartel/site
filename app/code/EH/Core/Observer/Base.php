<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2018 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Observer;

use Magento\Framework\Event\ObserverInterface;
use EH\Core\Model\FeedFactory;
use Magento\Backend\Model\Auth\Session as BackendAuthSession;
use Magento\Framework\Module\ModuleListInterface;
use EH\Core\Helper\Data as CoreHelper;
use Magento\Framework\Registry;

abstract class Base implements ObserverInterface
{
    /**
     * @var \EH\Core\Model\FeedFactory
     */
    protected $_feedFactory;

    /**
     * @var BackendAuthSession
     */
    protected $_backendAuthSession;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param FeedFactory $feedFactory
     * @param BackendAuthSession $backendAuthSession
     * @param ModuleListInterface $moduleList
     * @param CoreHelper $coreHelper
     * @param Registry $registry
     */
    public function __construct(
        FeedFactory $feedFactory,
        BackendAuthSession $backendAuthSession,
        ModuleListInterface $moduleList,
        CoreHelper $coreHelper,
        Registry $registry
    ) {
        $this->_feedFactory = $feedFactory;
        $this->moduleList = $moduleList;
        $this->registry = $registry;
        $this->coreHelper = $coreHelper;
        $this->_backendAuthSession = $backendAuthSession;
    }

    /**
     * Filter extension
     *
     * @param $extensionNames
     * @return string
     */
    protected function filterExtensions($extensionNames)
    {
        $prefix = "\x45\x48\x5f";
        return preg_grep("/$prefix/", $extensionNames);
    }
}
