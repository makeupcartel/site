<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Adminhtml\Category\Edit\Button;

use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Block\Adminhtml\Category\Edit\Button\Delete as DeleteButton;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;

/**
 * Test for \Aheadworks\Blog\Block\Adminhtml\Category\Edit\Button\Delete
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Button constants defined for test
     */
    const DELETE_URL = 'http://localhost/blog/category/delete/id/1';
    const CATEGORY_ID = 1;
    /**#@-*/

    /**
     * @var DeleteButton
     */
    private $button;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);

        $categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $categoryRepositoryMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo(self::CATEGORY_ID))
            ->will($this->returnValue($this->getMockForAbstractClass(CategoryInterface::class)));

        $this->button = $objectManager->getObject(
            DeleteButton::class,
            [
                'request' => $this->requestMock,
                'urlBuilder' => $this->urlBuilderMock,
                'categoryRepository' => $categoryRepositoryMock
            ]
        );
    }

    /**
     * Testing of return value of getButtonData method
     *
     * @dataProvider getButtonDataDataProvider
     * @param int|null $categoryId
     */
    public function testGetButtonData($categoryId)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn($categoryId);
        if ($categoryId) {
            $this->urlBuilderMock->expects($this->once())
                ->method('getUrl')
                ->with(
                    $this->equalTo('*/*/delete'),
                    $this->equalTo(['id' => self::CATEGORY_ID])
                )
                ->will($this->returnValue(self::DELETE_URL));
            $this->assertNotEmpty($this->button->getButtonData());
        } else {
            $this->assertEmpty($this->button->getButtonData());
        }
    }

    /**
     * Data provider for testGetButtonData method
     *
     * @return array
     */
    public function getButtonDataDataProvider()
    {
        return [
            'category id specified' => [self::CATEGORY_ID],
            'category id not specified' => [null]
        ];
    }
}
