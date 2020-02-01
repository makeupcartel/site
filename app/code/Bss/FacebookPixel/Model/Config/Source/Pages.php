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
 * @category  BSS
 * @package   Bss_FacebookPixel
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FacebookPixel\Model\Config\Source;

class Pages implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'cms_page', 'label' => 'Cms Page'],
            ['value' => 'account_page', 'label' => 'Account Page'],
            ['value' => 'registration_page', 'label' => 'Registration Page'],
            ['value' => 'checkout_page', 'label' => 'Checkout Page'],
            ['value' => 'success_page', 'label' => 'Success Page'],
            ['value' => 'search_page', 'label' => 'Search Page'],
            ['value' => 'advanced_search_page', 'label' => 'Advanced Search Page']
        ];
    }
}
