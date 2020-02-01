<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Controller\Adminhtml\Post;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Controller\Adminhtml\Post\Validate;
use Magento\Framework\Controller\Result\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Validator\Exception;
use Aheadworks\Blog\Model\Post;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\Data\PostInterfaceFactory;
use Aheadworks\Blog\Model\PostFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Message\Error;
use Magento\Framework\App\Request\Http;
use Magento\User\Model\User;
use Magento\Backend\Model\Auth;
use Magento\Backend\App\Action\Context;
use Aheadworks\Blog\Model\Converter\Condition as ConditionConverter;
use Aheadworks\Blog\Api\Data\ConditionInterface;

/**
 * Test for \Aheadworks\Blog\Controller\Adminhtml\Post\Validate
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const POST_ID = 1;
    const USER_ID = 1;
    const USER_NAME = 'Admin Admin';
    const STORE_ID = 1;
    const ERROR_MESSAGE = 'Value is invalid.';
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
        'product_condition' => ''
    ];

    /**
     * @var Validate
     */
    private $action;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var Exception|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exceptionMock;

    /**
     * @var Post|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postModelMock;

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

        $postMock = $this->getMockForAbstractClass(PostInterface::class);
        $postMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::POST_ID));
        $postDataFactoryMock = $this->getMockBuilder(PostInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $postDataFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($postMock));

        $this->postModelMock = $this->getMockBuilder(Post::class)
            ->setMethods(['setData', 'setPostId', 'validateBeforeSave'])
            ->disableOriginalConstructor()
            ->getMock();
        $postFactoryMock = $this->getMockBuilder(PostFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $postFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->postModelMock));

        $dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->setMethods(['buildOutputDataArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonMock->expects($this->any())
            ->method('setData')
            ->will($this->returnSelf());
        $resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultJsonMock));

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

        $errorMock = $this->getMockBuilder(Error::class)
            ->setMethods(['getText'])
            ->disableOriginalConstructor()
            ->getMock();
        $errorMock->expects($this->any())
            ->method('getText')
            ->will($this->returnValue(self::ERROR_MESSAGE));
        $this->exceptionMock = $this->getMockBuilder(Exception::class)
            ->setMethods(['getMessages'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->exceptionMock->expects($this->any())
            ->method('getMessages')
            ->with(MessageInterface::TYPE_ERROR)
            ->will($this->returnValue([$errorMock]));

        $requestMock = $this->getMockBuilder(Http::class)
            ->setMethods(['getPostValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->expects($this->any())
            ->method('getPostValue')
            ->will($this->returnValue($this->formData));

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
        $this->conditionConverterMock = $this->getMockBuilder(ConditionConverter::class)
            ->setMethods(['arrayToDataModel'])
            ->disableOriginalConstructor()
            ->getMock();
        $conditionMock = $this->getMockForAbstractClass(ConditionInterface::class);
        $this->conditionConverterMock->expects($this->once())
            ->method('arrayToDataModel')
            ->willReturn($conditionMock);

        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $requestMock,
                'auth' => $authMock
            ]
        );

        $this->action = $objectManager->getObject(
            Validate::class,
            [
                'context' => $context,
                'postDataFactory' => $postDataFactoryMock,
                'postFactory' => $postFactoryMock,
                'dataObjectHelper' => $dataObjectHelperMock,
                'dataObjectProcessor' => $dataObjectProcessorMock,
                'resultJsonFactory' => $resultJsonFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'conditionConverter' => $this->conditionConverterMock
            ]
        );
    }

    /**
     * Testing of return value of execute method
     */
    public function testExecuteResult()
    {
        $this->assertSame($this->resultJsonMock, $this->action->execute());
    }

    /**
     * Testing of successful validation result
     */
    public function testExecuteSuccess()
    {
        $this->resultJsonMock->expects($this->atLeastOnce())
            ->method('setData')
            ->with(
                $this->callback(
                    function ($response) {
                        return !$response->getError();
                    }
                )
            );
        $this->action->execute();
    }

    /**
     * Testing of unsuccessful validation result
     */
    public function testExecuteFail()
    {
        $this->postModelMock->expects($this->any())
            ->method('validateBeforeSave')
            ->willThrowException($this->exceptionMock);
        $this->resultJsonMock->expects($this->atLeastOnce())
            ->method('setData')
            ->with(
                $this->callback(
                    function ($response) {
                        return $response->getError() && $response->getMessages() == [self::ERROR_MESSAGE];
                    }
                )
            );
        $this->action->execute();
    }
}
