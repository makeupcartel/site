<?php

namespace Unirgy\SimpleUp\Model;

use \Magento\Backend\Model\Auth\Session;
use \Magento\Framework\Event\Observer as EventObserver;
use \Magento\Framework\Event\ObserverInterface;
use \Unirgy\SimpleUp\Helper\Data as HelperData;


class Observer implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var HelperData
     */
    protected $helper;

    public function __construct(
        Session $authSession,
        HelperData $helper
    )
    {
        $this->authSession = $authSession;
        $this->helper = $helper;

    }

    public function controller_action_predispatch(EventObserver $observer)
    {
        if ($this->authSession->isLoggedIn()) {
            $this->helper->checkUpdates();
        }
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $this->controller_action_predispatch($observer);
    }
}
