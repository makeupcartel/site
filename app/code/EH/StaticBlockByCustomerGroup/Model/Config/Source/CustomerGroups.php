<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2019 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_StaticBlockByCustomerGroup
 */

namespace EH\StaticBlockByCustomerGroup\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Convert\DataObject;

class CustomerGroups extends AbstractSource
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var DataObject
     */
    protected $_converter;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param DataObject $converter
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        DataObject $converter
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $groups = $this->_groupManagement->getLoggedInGroups();
            $groupsNotLoggedIn = $this->_groupManagement->getNotLoggedInGroup();
            $this->_options = $this->_converter->toOptionArray($groups, 'id', 'code');
            array_unshift($this->_options, ['value' => 'n', 'label' => $groupsNotLoggedIn->getCode()]);
        }
        return $this->_options;
    }

    public function getOptionText($value) {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}