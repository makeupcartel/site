<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\Source\Post;

use Aheadworks\Blog\Model\Source\Post\CustomerGroups;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Convert\DataObject;

class CustomerGroupsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectConverterMock;

    /**
     * @var CustomerGroups
     */
    private $sourceModel;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectConverterMock = $this->getMockBuilder(DataObject::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->sourceModel = $objectManager->getObject(
            CustomerGroups::class,
            [
                 'groupRepository' => $this->groupRepositoryMock,
                 'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                 'objectConverter' => $this->objectConverterMock
            ]
        );
    }

    /**
     * Testing of toOptionArray method
     */
    public function testToOptionArray()
    {
        $testOptions = [
            [
                'value' =>'test_value',
                'label' =>'test_label'
            ]
        ];

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteriaMock);
        $groupSearchResult = $this->getMockForAbstractClass(GroupSearchResultsInterface::class);
        $this->groupRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($groupSearchResult);

        $customerGroups[] = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)->getMock();
        $groupSearchResult->expects($this->once())
            ->method('getItems')
            ->willReturn($customerGroups);

        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($customerGroups, GroupInterface::ID, GroupInterface::CODE)
            ->willReturn($testOptions);

        $this->assertTrue(is_array($this->sourceModel->toOptionArray()));

        array_unshift($testOptions, $this->sourceModel->getAllGroupsOption());
        $this->assertEquals($testOptions, $this->sourceModel->toOptionArray());
    }
}
