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
namespace Bss\FacebookPixel\Block;

use Magento\Customer\CustomerData\SectionSourceInterface;

class Subscribe implements SectionSourceInterface
{
    /**
     * @var \Bss\FacebookPixel\Model\Session
     */
    protected $fbPixelSession;

    /**
     * Subscribe constructor.
     * @param \Bss\FacebookPixel\Model\Session $fbPixelSession
     */
    public function __construct(
        \Bss\FacebookPixel\Model\Session $fbPixelSession
    ) {
        $this->fbPixelSession = $fbPixelSession;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        $data = [
            'events' => []
        ];

        if ($this->fbPixelSession->hasAddSubscribe()) {
            $data['events'][] = [
                'eventName' => 'Subscribe',
                'eventAdditional' => $this->fbPixelSession->getAddSubscribe()
            ];
        }
        return $data;
    }
}
