<?php

namespace Unirgy\SimpleLicense\Model;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Message\ManagerInterface;
use Unirgy\SimpleUp\Block\Adminhtml\Module\Tabs;


class Observer implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    public function __construct(ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;

    }

    public function usimpleup_license_tabs(EventObserver $observer)
    {

        $ioncube = extension_loaded('ionCube Loader');
        $sg = function_exists('sg_get_const');
        if (!$ioncube && !$sg) {
            $this->messageManager->addError('ionCube or SourceGuardian Loader is not installed, uSimpleLicense is not activated.');
            return;
        }
        /** @var Tabs $container */
        $container = $observer->getEvent()->getData('container');

        $container->addTab('manage_licenses_section', [
            'label'     => __('Manage Licenses'),
            'title'     => __('Manage Licenses'),
            'content'   => $container->getLayout()->createBlock('Unirgy\SimpleLicense\Block\Adminhtml\License\Grid')->toHtml(),
        ]);

        $container->addTab('add_licenses_section', [
            'label'     => __('Add Licenses'),
            'title'     => __('Add Licenses'),
            'content'   => $container->getLayout()->createBlock('Magento\Backend\Block\Template')->setTemplate('Unirgy_SimpleLicense::usimplelic/add_licenses.phtml')->toHtml(),
        ]);
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
//        todo make this work
//        return;
        $this->usimpleup_license_tabs($observer);
    }
}
