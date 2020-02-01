<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Sidebar;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Block\Sidebar\Cms;
use Aheadworks\Blog\Model\Config;
use Magento\Cms\Model\Block as CmsBlock;
use Magento\Framework\Filter\Template;
use Magento\Cms\Model\BlockFactory as CmsBlockFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Test for \Aheadworks\Blog\Block\Sidebar\Cms
 */
class CmsTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Pager constants defined for test
     */
    const CMS_BLOCK_ID = 1;
    const CMS_BLOCK_CONTENT = '<p>Cms block content.</p>';
    const STORE_ID = 1;
    /**#@-*/

    /**
     * @var Cms
     */
    private $block;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var CmsBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cmsBlockMock;

    /**
     * @var Template|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getSidebarCmsBlockId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cmsBlockMock = $this->getMockBuilder(CmsBlock::class)
            ->setMethods(['setStoreId', 'load', 'getContent'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cmsBlockMock->expects($this->any())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $this->cmsBlockMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $cmsBlockFactoryMock = $this->getMockBuilder(CmsBlockFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $cmsBlockFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->cmsBlockMock));

        $this->filterMock = $this->getMockBuilder(Template::class)
            ->setMethods(['setStoreId', 'filter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterMock->expects($this->any())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $cmsFilterProviderMock = $this->getMockBuilder(FilterProvider::class)
            ->setMethods(['getBlockFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $cmsFilterProviderMock->expects($this->any())
            ->method('getBlockFilter')
            ->will($this->returnValue($this->filterMock));

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::STORE_ID));
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $context = $objectManager->getObject(
            Context::class,
            ['storeManager' => $storeManagerMock]
        );

        $this->block = $objectManager->getObject(
            Cms::class,
            [
                'context' => $context,
                'config' => $this->configMock,
                'cmsBlockFactory' => $cmsBlockFactoryMock,
                'cmsFilterProvider' => $cmsFilterProviderMock
            ]
        );
    }

    /**
     * Testing of retrieving of cms block instance
     */
    public function testGetCmsBlock()
    {
        $this->configMock->expects($this->any())
            ->method('getSidebarCmsBlockId')
            ->willReturn(self::CMS_BLOCK_ID);
        $this->assertEquals($this->cmsBlockMock, $this->block->getCmsBlock());
    }

    /**
     * Testing of retrieving of cms block html
     */
    public function testGetCmsBlockHtml()
    {
        $this->cmsBlockMock->expects($this->any())
            ->method('getContent')
            ->willReturn(self::CMS_BLOCK_CONTENT);
        $this->filterMock->expects($this->atLeastOnce())
            ->method('filter')
            ->with($this->equalTo(self::CMS_BLOCK_CONTENT))
            ->willReturn(self::CMS_BLOCK_CONTENT);
        $this->assertEquals(self::CMS_BLOCK_CONTENT, $this->block->getCmsBlockHtml($this->cmsBlockMock));
    }
}
