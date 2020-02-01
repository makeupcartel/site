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
namespace Bss\FacebookPixel\Observer;

use Magento\Framework\Event\ObserverInterface;

class Search implements ObserverInterface
{
    /**
     * @var \Bss\FacebookPixel\Model\Session
     */
    protected $fbPixelSession;
    /**
     * @var \Bss\FacebookPixel\Helper\Data
     */
    protected $fbPixelHelper;
    /**
     * @var \Magento\Search\Helper\Data
     */
    protected $searchHelper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Search constructor.
     * @param \Bss\FacebookPixel\Helper\Data $helper
     * @param \Magento\Search\Helper\Data $searchHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\FacebookPixel\Model\Session $fbPixelSession
     */
    public function __construct(
        \Bss\FacebookPixel\Helper\Data $helper,
        \Magento\Search\Helper\Data $searchHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Bss\FacebookPixel\Model\Session $fbPixelSession
    ) {
        $this->fbPixelSession = $fbPixelSession;
        $this->fbPixelHelper         = $helper;
        $this->searchHelper = $searchHelper;
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $text = $this->searchHelper->getEscapedQueryText();
        if (!$text) {
            $text = $this->request->getParams();
            foreach ($this->request->getParams() as $key => $value) {
                $text[$key] = $value;
            }
        }
        if (!$this->fbPixelHelper->getConfig('bss_facebook_pixel/event_tracking/search') || !$text) {
            return true;
        }

        $data = [
            'search_string' => $text
        ];
        $this->fbPixelSession->setSearch($data);

        return true;
    }
}
