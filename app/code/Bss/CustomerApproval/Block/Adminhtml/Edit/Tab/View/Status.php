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
namespace Bss\CustomerApproval\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Api\AccountManagementInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Status extends \Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo
{
    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     *
     */
    protected $customerMetaData;

    /**
     * @var \Magento\Customer\Model\Metadata\ElementFactory
     */
    protected $metadataElement;

    /**
     * Status constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Registry $registry
     * @param Mapper $addressMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Logger $customerLogger
     * @param \Magento\Customer\Model\Metadata\ElementFactory $metadataElement
     * @param \Magento\Customer\Api\CustomerMetadataInterface $customerMetaData
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        Mapper $addressMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Logger $customerLogger,
        \Magento\Customer\Model\Metadata\ElementFactory $metadataElement,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetaData,
        array $data = []
    ) {
        $this->customerMetaData = $customerMetaData;
        $this->metadataElement = $metadataElement;
        parent::__construct(
            $context,
            $accountManagement,
            $groupRepository,
            $customerDataFactory,
            $addressHelper,
            $dateTime,
            $registry,
            $addressMapper,
            $dataObjectHelper,
            $customerLogger,
            $data
        );
    }

    /**
     * Get All Attributes Customer
     *
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttribute()
    {
        return $this->customerMetaData->getAllAttributesMetadata('customer');
    }

    /**
     * Get customer status
     *
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStatus()
    {
        $customer = $this->getCustomer()->getCustomAttribute('activasion_status');
        if ($customer) {
            $customerValue = $customer->getValue();
            $attributes = $this->getAttribute();
            foreach ($attributes as $attribute) {
                if ($attribute->getAttributeCode() == 'activasion_status') {
                    $metadataElement = $this->metadataElement->create($attribute, $customerValue, 'customer');
                    $value = $metadataElement->outputValue(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML);
                }
            }
            return $value;
        }
    }
}
