<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2018 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Observer;

use Magento\Framework\Event\Observer;

class Event2 extends Base
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
            $feedModel = $this->_feedFactory->create();
            /* @var $feedModel \EH\Core\Model\Feed */
            $feedModel->checkUpdate();
            $extensionNames = $this->moduleList->getNames();
            $ourExtensions = $this->filterExtensions($extensionNames);
            foreach ($ourExtensions as $extensionName) {
                if(!$this->registry->registry($extensionName.'_l_message')) {
                    $this->registry->register($extensionName.'_l_message', 1);
                    $this->coreHelper->getExtensionVersion($extensionName, true);
                }
            }
        }
    }
}
