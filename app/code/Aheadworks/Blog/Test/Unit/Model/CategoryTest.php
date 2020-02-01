<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model;

use Aheadworks\Blog\Model\Category;
use Aheadworks\Blog\Model\Source\Category\Status;
use Aheadworks\Blog\Model\ResourceModel\Validator\UrlKeyIsUnique;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Blog\Model\Category
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Category
     */
    private $categoryModel;

    /**
     * @var UrlKeyIsUnique|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlKeyIsUniqueMock;

    /**
     * Category model data
     *
     * @var array
     */
    private $categoryData = [
        'name' => 'Category',
        'url_key' => 'cat',
        'status' => Status::ENABLED,
        'sort_order' => 0,
        'meta_title' => 'category meta title',
        'meta_description' => 'category meta description',
        'store_ids' => [1, 2]
    ];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlKeyIsUniqueMock = $this->getMockBuilder(UrlKeyIsUnique::class)
            ->setMethods(['validate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryModel = $objectManager->getObject(
            Category::class,
            ['urlKeyIsUnique' => $this->urlKeyIsUniqueMock]
        );
    }

    /**
     * Testing that proper exceptions are thrown if category data is incorrect
     *
     * @dataProvider validateBeforeSaveDataProvider
     * @param array $categoryData
     * @param string $exceptionMessage
     */
    public function testValidateBeforeSaveExceptions($categoryData, $exceptionMessage)
    {
        $this->urlKeyIsUniqueMock->expects($this->any())
            ->method('validate')
            ->with($this->equalTo($this->categoryModel))
            ->willReturn(true);
        $this->categoryModel->setData($categoryData);
        try {
            $this->categoryModel->validateBeforeSave();
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Magento\Framework\Validator\Exception::class, $e);
            $this->assertContains($exceptionMessage, $e->getMessage());
        }
    }

    /**
     * Testing that proper exception is thrown if category data contains duplicated URL-Key
     *
     * @expectedException \Magento\Framework\Validator\Exception
     * @expectedExceptionMessage This URL-Key is already assigned to another post, author or category.
     */
    public function testValidateBeforeSaveDuplicatedUrlKey()
    {
        $this->urlKeyIsUniqueMock->expects($this->any())
            ->method('validate')
            ->with($this->equalTo($this->categoryModel))
            ->willReturn(false);
        $this->categoryModel->setData($this->categoryData);
        $this->categoryModel->validateBeforeSave();
    }

    /**
     * Data provider for testValidateBeforeSaveExceptions method
     *
     * @return array
     */
    public function validateBeforeSaveDataProvider()
    {
        return [
            'empty name' => [
                array_merge($this->categoryData, ['name' => '']),
                'Name is required.'
            ],
            'empty URL-Key' => [
                array_merge($this->categoryData, ['url_key' => '']),
                'URL-Key is required.'
            ],
            'numeric URL-Key' => [
                array_merge($this->categoryData, ['url_key' => '123']),
                'URL-Key cannot consist only of numbers.'
            ],
            'invalid URL-Key' => [
                array_merge($this->categoryData, ['url_key' => 'invalid key*^']),
                'URL-Key cannot contain capital letters or disallowed symbols.'
            ],
            'empty stores' => [
                array_merge($this->categoryData, ['store_ids' => []]),
                'Select store view.'
            ]
        ];
    }
}
