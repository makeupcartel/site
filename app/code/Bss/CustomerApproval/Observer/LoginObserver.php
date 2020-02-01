<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerApproval
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerApproval\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Bss\CustomerApproval\Helper\Data;
use Magento\Framework\Exception\EmailNotConfirmedException;

class LoginObserver implements ObserverInterface
{
    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Bss\CustomerApproval\Helper\Data
     */
    protected $helper;

    /**
     * LoginObserver constructor.
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param Data $helper
     */
    public function __construct(
        ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request,
        Data $helper
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws EmailNotConfirmedException
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $orderValue = $observer->getModel()->getData('activasion_status');
            if ($orderValue) {
                $orderValue = (int) $orderValue;
                $pending = $this->helper->getPendingValue();
                $disapprove = $this->helper->getDisApproveValue();
                
                if ($orderValue == $pending) {
                    $message = $this->helper->getPendingMess();
                    if ($this->request->isAjax()) {
                        throw new EmailNotConfirmedException(__($message));
                    }
                }
                if ($orderValue == $disapprove) {
                    $message = $this->helper->getDisapproveMess();
                    if ($this->request->isAjax()) {
                        throw new EmailNotConfirmedException(__($message));
                    }
                }
            }
        }
    }
}
