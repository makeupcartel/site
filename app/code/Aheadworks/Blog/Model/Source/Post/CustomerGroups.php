<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source\Post;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Customer\Api\Data\GroupInterface;

/**
 * Class CustomerGroups
 * @package Aheadworks\Blog\Model\Source\Post
 */
class CustomerGroups implements OptionSourceInterface
{
    /**
     * Constant for 'All Groups' option
     */
    const ALL_GROUPS = 'all';

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DataObject
     */
    private $objectConverter;

    /**
     * @var array
     */
    private $options;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObject $objectConverter
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject $objectConverter
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
    }

    /**
     * Prepare 'All Groups' option
     *
     * @return array
     */
    public function getAllGroupsOption()
    {
        return [
            'value' => self::ALL_GROUPS,
            'label' =>__('All Groups')
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            $this->options = $this->objectConverter->toOptionArray(
                $customerGroups,
                GroupInterface::ID,
                GroupInterface::CODE
            );
            array_unshift($this->options, $this->getAllGroupsOption());
        }
        return $this->options;
    }
}
