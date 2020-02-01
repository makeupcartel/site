<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2018 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Observer;

use Magento\Framework\Event\Observer;

class Event1 extends Base
{

    /**
     * Predispath admin action controller
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            $extensionNames = $this->moduleList->getNames();
            $ourExtensions = $this->filterExtensions($extensionNames);
            $this->coreHelper->prepareExtensionVersions($ourExtensions);
        }
    }
}
