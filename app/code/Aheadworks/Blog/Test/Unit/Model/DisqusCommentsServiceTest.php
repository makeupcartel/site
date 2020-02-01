<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model;

use Aheadworks\Blog\Model\Disqus\Api;
use Aheadworks\Blog\Model\DisqusCommentsService;
use Aheadworks\Blog\Model\DisqusConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Test for \Aheadworks\Blog\Model\DisqusCommentsService
 */
class DisqusCommentsServiceTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const STORE_ID = 1;
    const WEBSITE_ID = 2;
    const FORUM_CODE = 'forum_code';
    /**#@-*/

    /**
     * @var DisqusCommentsService
     */
    private $commentsService;

    /**
     * @var Api|\PHPUnit_Framework_MockObject_MockObject
     */
    private $disqusApiMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $disqusConfigMock = $this->getMockBuilder(DisqusConfig::class)
            ->setMethods(['getForumCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $disqusConfigMock->expects($this->any())
            ->method('getForumCode')
            ->with(self::WEBSITE_ID)
            ->will($this->returnValue(self::FORUM_CODE));

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(self::WEBSITE_ID));
        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::WEBSITE_ID));
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(self::STORE_ID)
            ->will($this->returnValue($storeMock));

        $this->disqusApiMock = $this->getMockBuilder(Api::class)
            ->setMethods(['sendRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->commentsService = $objectManager->getObject(
            DisqusCommentsService::class,
            [
                'disqusConfig' => $disqusConfigMock,
                'storeManager' => $storeManagerMock,
                'disqusApi' => $this->disqusApiMock
            ]
        );
    }

    /**
     * Test of retrieving total number published comments for a post
     */
    public function testGetPublishedCommNum()
    {
        $postId = 1;
        $posts = 5;
        $this->disqusApiMock->expects($this->once())
            ->method('sendRequest')
            ->with(
                Api::RES_THREADS_DETAILS,
                ['forum' => self::FORUM_CODE, 'thread:ident' => $postId]
            )->willReturn(['posts' => $posts]);
        $this->assertEquals(
            $posts,
            $this->commentsService->getPublishedCommNum($postId, self::STORE_ID)
        );
        $this->commentsService->getPublishedCommNum($postId, self::STORE_ID);
    }

    /**
     * Test of retrieving number published comments for posts.
     *
     * @dataProvider getPublishedCommNumBundleDataProvider
     */
    public function testGetPublishedCommNumBundle($postIds, $threadIds, $threads, $postList, $expected)
    {
        $this->prepareDisqusApiMock(
            $this->disqusApiMock,
            $postIds,
            $threadIds,
            $threads,
            $postList
        );
        $this->assertEquals(
            $expected,
            $this->commentsService->getPublishedCommNumBundle($postIds, self::STORE_ID)
        );
        $this->commentsService->getPublishedCommNumBundle($postIds, self::STORE_ID);
    }

    /**
     * Test of retrieving number new comments for posts.
     *
     * @dataProvider getNewCommNumBundleDataProvider
     */
    public function testGetNewCommNumBundle($postIds, $threadIds, $threads, $postList, $expected)
    {
        $this->prepareDisqusApiMock(
            $this->disqusApiMock,
            $postIds,
            $threadIds,
            $threads,
            $postList
        );
        $this->assertEquals(
            $expected,
            $this->commentsService->getNewCommNumBundle($postIds, self::STORE_ID)
        );
        $this->commentsService->getNewCommNumBundle($postIds, self::STORE_ID);
    }

    /**
     * Prepares mock of \Aheadworks\Blog\Model\Disqus\Api class
     *
     * @param Api|\PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $postIds
     * @param array $threadIds
     * @param array $threads
     * @param array $postList
     * @return void
     */
    private function prepareDisqusApiMock($mock, $postIds, $threadIds, $threads, $postList)
    {
        $mock->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnMap(
                [
                    [
                        Api::RES_FORUMS_LIST_THREADS,
                        [
                            'forum' => self::FORUM_CODE,
                            'thread:ident' => $postIds,
                            'related' => [Api::RELATION_FORUM],
                            'include' => [Api::THREAD_STATUS_OPEN]
                        ],
                        $threads
                    ],
                    [
                        Api::RES_POSTS_LIST,
                        [
                            'forum' => self::FORUM_CODE,
                            'thread' => $threadIds,
                            'related' => [Api::RELATION_THREAD],
                            'include' => [Api::POST_STATUS_APPROVED, Api::POST_STATUS_UNAPPROVED]
                        ],
                        $postList
                    ]
                ]
            );
    }

    /**
     * Data provider for testGetPublishedCommNumBundle method
     *
     * @return array
     */
    public function getPublishedCommNumBundleDataProvider()
    {
        return [
            [
                [1, 2, 3],
                [10, 20, 30],
                [
                    ['identifiers' => [1], 'id' => 10],
                    ['identifiers' => [2], 'id' => 20],
                    ['identifiers' => [3], 'id' => 30]
                ],
                [
                    ['thread' => ['id' => 10], 'isApproved' => true],
                    ['thread' => ['id' => 20], 'isApproved' => true],
                    ['thread' => ['id' => 30], 'isApproved' => false],
                    ['thread' => ['id' => 10], 'isApproved' => true]
                ],
                [1 => 2, 2 => 1, 3 => 0]
            ]
        ];
    }

    /**
     * Data provider for testGetNewCommNumBundle method
     *
     * @return array
     */
    public function getNewCommNumBundleDataProvider()
    {
        return [
            [
                [1, 2, 3],
                [10, 20, 30],
                [
                    ['identifiers' => [1], 'id' => 10],
                    ['identifiers' => [2], 'id' => 20],
                    ['identifiers' => [3], 'id' => 30]
                ],
                [
                    ['thread' => ['id' => 10], 'isApproved' => false],
                    ['thread' => ['id' => 10], 'isApproved' => true],
                    ['thread' => ['id' => 20], 'isApproved' => false],
                    ['thread' => ['id' => 20], 'isApproved' => false],
                    ['thread' => ['id' => 30], 'isApproved' => true]
                ],
                [1 => 1, 2 => 2, 3 => 0]
            ]
        ];
    }
}
