<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\Post\Author;

use Aheadworks\Blog\Api\Data\AuthorSearchResultsInterface;
use Aheadworks\Blog\Model\Post\Author\Creator;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\Post\Author\Resolver;
use PHPUnit\Framework\TestCase;
use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Api\AuthorRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class ResolverTest
 * @package Aheadworks\Blog\Test\Unit\Model\Post\Author
 */
class ResolverTest extends TestCase
{
    /**
     * @var AuthorRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var Creator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creatorMock;

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->authorRepositoryMock = $this->createMock(AuthorRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->creatorMock = $this->createMock(Creator::class);
        $this->resolver = $objectManager->getObject(
            Resolver::class,
            [
                'authorRepository' => $this->authorRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'creator' => $this->creatorMock
            ]
        );
    }

    /**
     * Test resolveId method
     *
     * @param bool $thrownException
     * @dataProvider boolProvider
     */
    public function testResolveId($thrownException)
    {
        $authorId = $thrownException ? null : 1;
        $authorMock = $this->createMock(AuthorInterface::class);
        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchResultsMock = $this->createMock(AuthorSearchResultsInterface::class);
        $authors = [];

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);
        $this->authorRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($authors);
        if ($thrownException) {
            $this->creatorMock->expects($this->once())
                ->method('createByName')
                ->withAnyParameters()
                ->willThrowException(new \Exception('Cannot create author.'));
        } else {
            $authorMock->expects($this->once())
                ->method('getId')
                ->willReturn($authorId);
            $this->creatorMock->expects($this->once())
                ->method('createByName')
                ->withAnyParameters()
                ->willReturn($authorMock);
        }

        $this->assertEquals($authorId, $this->resolver->resolveId(['name' => 'Test Customer'], 'name'));
    }

    /**
     * Test resolveIdForWp method
     *
     * @param bool $thrownException
     * @dataProvider boolProvider
     */
    public function testResolveIdForWp($thrownException)
    {
        $authorId = $thrownException ? null : 1;
        $authorMock = $this->createMock(AuthorInterface::class);
        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchResultsMock = $this->createMock(AuthorSearchResultsInterface::class);
        $authors = [];

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);
        $this->authorRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($authors);
        if ($thrownException) {
            $this->creatorMock->expects($this->once())
                ->method('createByName')
                ->withAnyParameters()
                ->willThrowException(new \Exception('Cannot create author.'));
        } else {
            $authorMock->expects($this->once())
                ->method('getId')
                ->willReturn($authorId);
            $this->creatorMock->expects($this->once())
                ->method('createByName')
                ->withAnyParameters()
                ->willReturn($authorMock);
        }

        $this->assertEquals($authorId, $this->resolver->resolveIdForWp('Test Customer'));
    }

    /**
     * @return array
     */
    public function boolProvider()
    {
        return [[true], [false]];
    }
}
