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

use Magento\Framework\UrlInterface;
use Bss\CustomerApproval\Helper\Data;

class CustomerListing
{
    /**
     * CustomerListing constructor.
     * @param UrlInterface $urlBuilder
     * @param Data $helper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Data $helper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\View\Layout\Generic $subject
     * @param \Closure $proceed
     * @param string $component
     * @return array|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(\Magento\Framework\View\Layout\Generic $subject, \Closure $proceed, $component)
    {
        if ($component->getName() == 'customer_listing') {
            $result = $proceed($component);
            if (is_array($result)) {
                if (isset($result['components']['customer_listing']['children']['customer_listing']['children']
                    ['listing_top']['children']['listing_massaction'])) {
                    $approveUrl = $this->urlBuilder->getUrl(
                        'customerapproval/index/massApproved',
                        $paramsHere = []
                    );
                    $disApproveUrl = $this->urlBuilder->getUrl(
                        'customerapproval/index/massDisapproved',
                        $paramsHere = []
                    );
                    $approvedAction = [
                        'component' => 'uiComponent',
                        'type' => 'approve',
                        'label' => __('Approve'),
                        'url' => $approveUrl,
                        'confirm' => [
                            'title' => __('Approve Customer'),
                            'message' => __('Are you sure to Approve selected customers ?')
                        ]
                    ];

                    $disApprovedAction = [
                        'component' => 'uiComponent',
                        'type' => 'disapprove',
                        'label' => __('Disapprove'),
                        'url' => $disApproveUrl,
                        'confirm' => [
                            'title' => __('Disapproved Customer'),
                            'message' => __('Are you sure to Disapprove selected customers ?')
                        ]
                    ];

                    $result['components']['customer_listing']['children']['customer_listing']['children']
                    ['listing_top']['children']['listing_massaction']['config']['actions'][] = $approvedAction;

                    $result['components']['customer_listing']['children']['customer_listing']['children']
                    ['listing_top']['children']['listing_massaction']['config']['actions'][] = $disApprovedAction;
                }
            }
        }
        if (isset($result)) {
            return $result;
        }

        return $proceed($component);
    }
}
