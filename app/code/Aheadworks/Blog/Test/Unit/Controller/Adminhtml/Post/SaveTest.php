<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Controller\Adminhtml\Post;

use Aheadworks\Blog\Api\Data\ConditionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Controller\Adminhtml\Post\Save;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\Data\PostInterfaceFactory;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\Http;
use Magento\Backend\Model\Session;
use Magento\User\Model\User;
use Magento\Backend\Model\Auth;
use Magento\Backend\App\Action\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Aheadworks\Blog\Model\Converter\Condition as ConditionConverter;

/**
 * Test for \Aheadworks\Blog\Controller\Adminhtml\Post\Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const POST_ID = 1;
    const USER_ID = 1;
    const USER_NAME = 'Admin Admin';
    const STORE_ID = 1;
    /**#@-*/

    /**
     * @var array
     */
    private $formData = [
        'id' => self::POST_ID,
        'title' => 'Post',
        'has_short_content' => 'true',
        'status' => 'publication',
        'category_ids' => [],
        'tag_names' => [],
        'rule' => [
            'conditions' => [
                '1' => [
                    'type' => Aheadworks\Blog\Model\Rule\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => ''
                ],
                '1--1' => [
                    'type' => Aheadworks\Blog\Model\Rule\Condition\Product\Attributes::class,
                    'attribute' => 'category_ids',
                    'operator' => '==',
                    'value' => '23'
                ]
            ]
        ],
        'product_condition' => '',
        'featured_image_file' => '',
        'featured_image_title' => 'test title',
        'featured_image_alt' => 'alt text'
    ];

    /**
     * @var Save
     */
    private $action;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var PostRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postRepositoryMock;

    /**
     * @var PostInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postMock;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var ConditionConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionConverterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->postMock = $this->getMockForAbstractClass(PostInterface::class);
        $this->postMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::POST_ID));

        $this->postRepositoryMock = $this->getMockForAbstractClass(PostRepositoryInterface::class);
        $this->postRepositoryMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo(self::POST_ID))
            ->will($this->returnValue($this->postMock));
        $this->postRepositoryMock->expects($this->any())
            ->method('save')
            ->with($this->equalTo($this->postMock))
            ->will($this->returnValue($this->postMock));
        $postDataFactoryMock = $this->getMockBuilder(PostInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $postDataFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->postMock));

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->will($this->returnSelf());
        $resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultRedirectMock));

        $dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::STORE_ID));
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $this->storeManagerMock->expects($this->any())
            ->method('hasSingleStore')
            ->will($this->returnValue(false));

        $requestMock = $this->getMockBuilder(Http::class)
            ->setMethods(['getPostValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->expects($this->any())
            ->method('getPostValue')
            ->will($this->returnValue($this->formData));
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $sessionMock = $this->getMockBuilder(Session::class)
            ->setMethods(['unsFormData', 'setFormData'])
            ->disableOriginalConstructor()
            ->getMock();

        $userMock = $this->getMockBuilder(User::class)
            ->setMethods(['getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $userMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::USER_ID));
        $userMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::USER_NAME));
        $authMock = $this->getMockBuilder(Auth::class)
            ->setMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();
        $authMock->expects($this->any())->method('getUser')->will($this->returnValue($userMock));
        $this->dataPersistorMock = $this->getMockForAbstractClass(DataPersistorInterface::class);
        $this->conditionConverterMock = $this->getMockBuilder(ConditionConverter::class)
            ->setMethods(['arrayToDataModel'])
            ->disableOriginalConstructor()
            ->getMock();

        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $requestMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $resultRedirectFactoryMock,
                'session' => $sessionMock,
                'auth' => $authMock
            ]
        );

        $this->action = $objectManager->getObject(
            Save::class,
            [
                'context' => $context,
                'postRepository' => $this->postRepositoryMock,
                'postDataFactory' => $postDataFactoryMock,
                'dataObjectHelper' => $dataObjectHelperMock,
                'storeManager' => $this->storeManagerMock,
                'dataPersistor' => $this->dataPersistorMock,
                'conditionConverter' => $this->conditionConverterMock
            ]
        );
    }

    /**
     * Testing of redirect while saving
     */
    public function testExecuteRedirect()
    {
        $this->resultRedirectMock->expects($this->atLeastOnce())
            ->method('setPath')
            ->with($this->equalTo('*/*/'));
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing of redirect if error is occur
     */
    public function testExecuteRedirectError()
    {
        $this->postRepositoryMock->expects($this->any())
            ->method('save')
            ->willThrowException(
                new \Magento\Framework\Validator\Exception()
            );
        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with('aw_blog_post', $this->formData);
        $this->resultRedirectMock->expects($this->atLeastOnce())
            ->method('setPath')
            ->with($this->equalTo('*/*/edit'));
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing that post saved
     */
    public function testExecutePostSave()
    {
        $this->postRepositoryMock->expects($this->atLeastOnce())
            ->method('save')
            ->with($this->equalTo($this->postMock));
        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('aw_blog_post');
        $conditionMock = $this->getMockForAbstractClass(ConditionInterface::class);
        $this->conditionConverterMock->expects($this->once())
            ->method('arrayToDataModel')
            ->willReturn($conditionMock);
        $this->action->execute();
    }

    /**
     * Testing that success message is added if post is saved
     */
    public function testExecuteSuccessMessage()
    {
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage');
        $this->action->execute();
    }
}
