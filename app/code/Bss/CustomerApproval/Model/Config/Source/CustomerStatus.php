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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerApproval\Model\Config\Source;

class CustomerStatus extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{
    /**
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity
     */
    protected $eavAttrEntity;

    /**
     *
     * @var \Bss\CustomerApproval\Model\ResourceModel\Options $optionModel
     */
    protected $optionModel;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity
     * @param \Bss\CustomerApproval\Model\ResourceModel\Options $optionModel
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity,
        \Bss\CustomerApproval\Model\ResourceModel\Options $optionModel
    ) {
        parent::__construct($eavAttrEntity);
        $this->optionModel = $optionModel;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $pendingValue = (int) $this->optionModel->getStatusValue('Pending')['option_id'];
        $approveValue = (int) $this->optionModel->getStatusValue('Approved')['option_id'];
        $disapproveValue = (int) $this->optionModel->getStatusValue('Disapproved')['option_id'];
        
        $options = [];

        $options[] = [
            'label' => __('Pending'),
            'value' => $pendingValue,
        ];
        $options[] = [
            'label' => __('Approved'),
            'value' => $approveValue,
        ];
        $options[] = [
            'label' => __('Disapproved'),
            'value' => $disapproveValue,
        ];
        
        return $options;
    }
}