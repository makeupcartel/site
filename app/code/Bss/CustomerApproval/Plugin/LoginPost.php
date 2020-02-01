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
namespace Bss\CustomerApproval\Plugin;

use Bss\CustomerApproval\Helper\Data;
use Magento\Framework\Registry as CoreRegistry;

class LoginPost
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CoreRegistry
     */
    protected $registry;

    /**
     * LoginPost constructor.
     * @param Data $helper
     * @param CoreRegistry $registry
     */
    public function __construct(
        Data $helper,
        CoreRegistry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Closure $proceed
     * @return $this|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        if ($this->helper->isEnable()) {
            $this->registry->unregister('bss_check_request_type');
            $this->registry->register('bss_check_request_type', 'login');
        }

        return $proceed();
    }
}
