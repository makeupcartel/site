<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Api\Controller;

use Magento\Framework\App\ActionInterface;

/**
 * Interface LoginCheckInterface
 *
 * @package BitExpert\ForceCustomerLogin\Api\Controller
 */
interface LoginCheckInterface extends ActionInterface
{
    /*
     * Configuration
     */
    const MODULE_CONFIG_TARGET = 'customer/BitExpert_ForceCustomerLogin/url';
}
