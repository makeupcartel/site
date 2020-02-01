<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Observer;

use Magento\Framework\Event\Observer;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Amasty\SocialLogin\Model\Repository\SalesRepository
     */
    private $salesRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\SocialLogin\Model\Repository\SalesRepository $salesRepository
    ) {
        $this->session = $session;
        $this->salesRepository = $salesRepository;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $userProfile = $this->session->getAmSocialIdentifier();
        if ($order && $userProfile) {
            try {
                $this->salesRepository->createItem($order, $userProfile);
            } catch (\Exception $ex) {
                $this->logger->critical($ex);
            }
        }
    }
}
