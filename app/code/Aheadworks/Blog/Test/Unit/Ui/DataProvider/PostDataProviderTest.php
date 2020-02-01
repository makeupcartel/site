<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Ui\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Blog\Ui\DataProvider\PostDataProvider;
use Aheadworks\Blog\Model\ResourceModel\Post\Grid\CollectionFactory;
use Aheadworks\Blog\Model\ResourceModel\Post\Grid\Collection;
use Aheadworks\Blog\Model\Post;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Test for \Aheadworks\Blog\Ui\DataProvider\PostDataProvider
 */
class PostDataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Post constants defined for test
     */
    const DATA_PROVIDER_NAME = 'post_listing_data_source';
    const PRIMARY_FIELD_NAME = 'post_id';
    const REQUEST_FIELD_NAME = 'post_id';
    const POST_ID = 1;
    /**#@-*/

    /**
     * @var array
     */
    private $postData = [
        'post_id' => self::POST_ID,
        'store_ids' => [1, 2],
        'status' => 'draft',
        'is_allow_comments' => 1,
        'tag_names' => ['tag1', 'tag2'],
        'category_ids' => [1, 2],
        'meta_twitter_site' => '@testsite'
    ];

    /**
     * @var PostDataProvider
     */
    private $dataProvider;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var Collection
     */
    private $collectionMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->setMethods(['getItems', 'getNewEmptyItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->collectionMock));
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->dataPersistorMock = $this->getMockForAbstractClass(DataPersistorInterface::class);

        $this->dataProvider = $objectManager->getObject(
            PostDataProvider::class,
            [
                'name' => self::DATA_PROVIDER_NAME,
                'primaryFieldName' => self::PRIMARY_FIELD_NAME,
                'requestFieldName' => self::REQUEST_FIELD_NAME,
                'collectionFactory' => $collectionFactoryMock,
                'request' => $this->requestMock,
                'dataPersistor' => $this->dataPersistorMock
            ]
        );
    }

    /**
     * Testing of get data from collection
     */
    public function testGetDataFromCollection()
    {
        $postMock = $this->getMockBuilder(Post::class)
            ->setMethods(['getId', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $postMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(self::POST_ID));
        $postMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($this->postData));

        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$postMock]));
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo(self::REQUEST_FIELD_NAME))
            ->will($this->returnValue(self::POST_ID));

        $data = $this->dataProvider->getData();
        $this->assertArrayHasKey(self::POST_ID, $data);
    }

    /**
     * Testing of get data from DataPersistor
     */
    public function testGetDataFromDataPersistor()
    {
        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('aw_blog_post')
            ->willReturn($this->postData);
        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('aw_blog_post')
            ->willReturnSelf();

        $postMock = $this->getMockBuilder(Post::class)
            ->setMethods(['getId', 'getData', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $postMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(self::POST_ID));
        $postMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($this->postData));
        $postMock->expects($this->once())
            ->method('setData')
            ->with($this->postData)
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getNewEmptyItem')
            ->will($this->returnValue($postMock));

        $data = $this->dataProvider->getData();
        $this->assertArrayHasKey(self::POST_ID, $data);
    }
}
