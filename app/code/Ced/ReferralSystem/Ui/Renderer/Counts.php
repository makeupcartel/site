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
 * Class Counts
 * @package Ced\ReferralSystem\Ui\Renderer
 */
class Counts extends Column
{
    const REGISTERED = 1;
    const UNREGISTERED = 2;


    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

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
        \Ced\ReferralSystem\Model\ReferList $referList,
        \Magento\Customer\Model\Customer $customerModel,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->referList = $referList;
        $this->customerModel = $customerModel;
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
                $item['by_emails'] = 0;
                $item['pending'] = 0;

                $customerId = $item['entity_id'];
                $pendingreferral = $this->referList->getCollection()
                                        ->addFieldtoFilter ( 'customer_id', $customerId )
                                        ->addFieldtoFilter('signup_status', self::UNREGISTERED)
                                        ->getAllIds();
                $item['pending'] = count($pendingreferral);

                $customer = $this->customerModel->load($customerId);
                if($customer && $customer->getId()){
                    $invitationCode = $customer->getInvitationCode();
                    if($invitationCode!=''){
                        $allCustomers = $this->customerModel
                            ->getCollection()
                            ->addFieldtoFilter('referral_code', $invitationCode)
                            ->getAllIds();
                        if(!empty($allCustomers)){
                            $item['by_emails'] = count($allCustomers);
                        }
                    }
                }
            }
        }
        return $dataSource;
    }
}