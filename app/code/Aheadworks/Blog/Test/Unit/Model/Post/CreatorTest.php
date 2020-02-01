<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\Post\Author;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\Post\Author\Creator;
use PHPUnit\Framework\TestCase;
use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Api\Data\AuthorInterfaceFactory;
use Aheadworks\Blog\Api\AuthorRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Blog\Model\ResourceModel\Validator\UrlKeyIsUnique as UrlKeyValidator;

/**
 * Class CreatorTest
 * @package Aheadworks\Blog\Test\Unit\Model\Post\Author
 */
class CreatorTest extends TestCase
{
    /**
     * @var AuthorRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorRepositoryMock;

    /**
     * @var AuthorInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorDataFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var UrlKeyValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlKeyValidatorMock;

    /**
     * @var Creator
     */
    private $creator;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->authorRepositoryMock = $this->createMock(AuthorRepositoryInterface::class);
        $this->authorDataFactoryMock = $this->createMock(AuthorInterfaceFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->urlKeyValidatorMock = $this->createMock(UrlKeyValidator::class);
        $this->creator = $objectManager->getObject(
            Creator::class,
            [
                'authorRepository' => $this->authorRepositoryMock,
                'authorDataFactory' => $this->authorDataFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'urlKeyValidator' => $this->urlKeyValidatorMock,
            ]
        );
    }

    /**
     * Test createByName method
     *
     * @param bool $isReachedMaxTries
     * @throws \Exception
     * @dataProvider createByNameProvider
     */
    public function testCreateByName($isReachedMaxTries)
    {
        $authorMock = $this->createMock(AuthorInterface::class);
        $exception = new LocalizedException(__('Url Key is not unique.'));
        $fullName = 'Test Customer';

        $authorMock->expects($this->once())
            ->method('setFirstname')
            ->withAnyParameters()
            ->willReturnSelf();
        $authorMock->expects($this->once())
            ->method('setLastname')
            ->withAnyParameters()
            ->willReturnSelf();
        $authorMock->expects($this->atLeastOnce())
            ->method('setUrlKey')
            ->withAnyParameters()
            ->willReturnSelf();
        $this->authorDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($authorMock);
        $this->urlKeyValidatorMock->expects($this->atLeastOnce())
            ->method('validate')
            ->with($authorMock)
            ->willReturn($isReachedMaxTries ? false : true);
        if ($isReachedMaxTries) {
            $this->authorRepositoryMock->expects($this->once())
                ->method('save')
                ->with($authorMock)
                ->willThrowException($exception);
            $this->expectException(LocalizedException::class);
            $this->expectExceptionMessage('Url Key is not unique.');
            $this->creator->createByName($fullName);
        } else {
            $this->authorRepositoryMock->expects($this->once())
                ->method('save')
                ->with($authorMock)
                ->willReturn($authorMock);
            $this->assertEquals($authorMock, $this->creator->createByName($fullName));
        }
    }

    /**
     * Test createByName method with exception
     *
     * @param string $fullName
     * @param bool $isInvalidFullName
     * @throws \Exception
     * @dataProvider createByNameProviderWithException
     */
    public function testCreateByNameWithException($fullName, $isInvalidFullName)
    {
        $authorMock = $this->createMock(AuthorInterface::class);
        $message = $isInvalidFullName ? 'Author name is invalid.' : 'Url Key is not unique.';
        $exception = $isInvalidFullName ? new LocalizedException(__($message)) : new \Exception($message);

        if (!$isInvalidFullName) {
            $authorMock->expects($this->once())
                ->method('setFirstname')
                ->withAnyParameters()
                ->willReturnSelf();
            $authorMock->expects($this->once())
                ->method('setLastname')
                ->withAnyParameters()
                ->willReturnSelf();
            $authorMock->expects($this->atLeastOnce())
                ->method('setUrlKey')
                ->withAnyParameters()
                ->willReturnSelf();
            $this->authorDataFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($authorMock);
            $this->urlKeyValidatorMock->expects($this->atLeastOnce())
                ->method('validate')
                ->with($authorMock)
                ->willThrowException($exception);
        }
        $this->expectException(get_class($exception));
        $this->expectExceptionMessage($message);
        $this->creator->createByName($fullName);
    }

    /**
     * @return array
     */
    public function createByNameProviderWithException()
    {
        return [
            ['Test', true],
            ['Test Customer', false]
        ];
    }

    /**
     * @return array
     */
    public function createByNameProvider()
    {
        return [[true], [false]];
    }
}
