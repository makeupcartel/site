<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\Model\ResourceModel\TagCloudItem as ResourceTagCloudItem;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Blog\Model\ResourceModel\TagCloudItem
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TagCloudItemTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const CONNECTION_NAME = 'default';
    const POST_TAG_LINKAGE_TABLE_NAME = 'aw_blog_post_tag';
    const POST_STORE_LINKAGE_TABLE_NAME = 'aw_blog_post_store';
    /**#@-*/

    /**
     * @var ResourceTagCloudItem
     */
    private $tagCloudItemResourceModel;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->setMethods(['from', 'joinLeft', 'where', 'columns'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->selectMock));
        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $metadataMock->expects($this->any())
            ->method('getEntityConnectionName')
            ->will($this->returnValue(self::CONNECTION_NAME));

        $metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->setMethods(['getMetadata'])
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with($this->equalTo(TagInterface::class))
            ->will($this->returnValue($metadataMock));

        $resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->setMethods(['getConnectionByName', 'getTableName'])
            ->disableOriginalConstructor()
            ->getMock();
        $resourceConnectionMock->expects($this->any())
            ->method('getConnectionByName')
            ->with(self::CONNECTION_NAME)
            ->will($this->returnValue($this->connectionMock));
        $resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            self::POST_TAG_LINKAGE_TABLE_NAME,
                            ResourceConnection::DEFAULT_CONNECTION,
                            self::POST_TAG_LINKAGE_TABLE_NAME
                        ],
                        [
                            self::POST_STORE_LINKAGE_TABLE_NAME,
                            ResourceConnection::DEFAULT_CONNECTION,
                            self::POST_STORE_LINKAGE_TABLE_NAME
                        ]
                    ]
                )
            );

        $transactionManagerMock = $this->getMockForAbstractClass(TransactionManagerInterface::class);
        $objectRelationProcessorMock = $this->getMockBuilder(ObjectRelationProcessor::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(['getTransactionManager', 'getResources', 'getObjectRelationProcessor'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getTransactionManager')
            ->will($this->returnValue($transactionManagerMock));
        $contextMock->expects($this->any())
            ->method('getResources')
            ->will($this->returnValue($resourceConnectionMock));
        $contextMock->expects($this->any())
            ->method('getObjectRelationProcessor')
            ->will($this->returnValue($objectRelationProcessorMock));

        $this->tagCloudItemResourceModel = $objectManager->getObject(
            ResourceTagCloudItem::class,
            [
                'metadataPool' => $metadataPoolMock,
                'context' => $contextMock
            ]
        );
    }

    /**
     * Testing of get connection
     */
    public function testGetConnection()
    {
        $this->assertSame($this->connectionMock, $this->tagCloudItemResourceModel->getConnection());
    }

    /**
     * Testing of retrieving of cloud item data
     */
    public function testGetPostCount()
    {
        $tagId = 1;
        $storeId = 2;
        $postCount = 10;

        $this->selectMock->expects($this->once())
            ->method('from')
            ->with(['post_tag_table' => self::POST_TAG_LINKAGE_TABLE_NAME], [])
            ->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['post_store_table' => self::POST_STORE_LINKAGE_TABLE_NAME],
                'post_tag_table.post_id = post_store_table.post_id',
                []
            )
            ->willReturnSelf();
        $this->selectMock->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(
                ['post_tag_table.tag_id = ?', $tagId],
                ['post_store_table.store_id IN (?)', [$storeId, \Magento\Store\Model\Store::DEFAULT_STORE_ID]]
            )
            ->willReturnSelf();
        $this->selectMock->expects($this->any())
            ->method('columns')
            ->willReturnSelf();
        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->selectMock)
            ->willReturn($postCount);
        $this->assertEquals(
            $postCount,
            $this->tagCloudItemResourceModel->getPostCount($tagId, $storeId)
        );
    }
}
