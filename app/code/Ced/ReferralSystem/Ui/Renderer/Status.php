<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_ReferralSystem
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\ReferralSystem\Ui\Renderer;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Status
 * @package Ced\ReferralSystem\Ui\Renderer
 */
class Status extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    const STATUS_USED = 1;
    const STATUS_UNUSED =2;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        \Magento\SalesRule\Model\Coupon $salesCoupon,
        \Ced\ReferralSystem\Model\Coupon $referralCoupon,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->salesCoupon = $salesCoupon;
        $this->referralCoupon = $referralCoupon;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['status'])) {
                    $coupon = $this->salesCoupon->load($item['coupon_code'], 'code');
                    if($coupon->getId()) {
                        $timesUsed = $coupon->getTimesUsed();
                        if($timesUsed>0){
                            $status = self::STATUS_USED;
                            $this->referralCoupon->load($item['id'])->setStatus($status)->save();
                        }else{
                            $status = self::STATUS_UNUSED;
                        }
                    }
                    $item['status']=$status;
                }
            }
        }
        return $dataSource;
    }
}