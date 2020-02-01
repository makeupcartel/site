<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model;

use Aheadworks\Blog\Model\DisqusConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Blog\Model\DisqusConfig
 */
class DisqusConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DisqusConfig
     */
    private $configModel;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->configModel = $objectManager->getObject(DisqusConfig::class, ['scopeConfig' => $this->scopeConfigMock]);
    }

    /**
     * Test get forum code
     */
    public function testGetForumCode()
    {
        $websiteId = 1;
        $forumCode = 'forumcode';
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(DisqusConfig::XML_PATH_DISQUS_FORUM_CODE, ScopeInterface::SCOPE_WEBSITE, $websiteId)
            ->willReturn($forumCode);
        $this->assertEquals($forumCode, $this->configModel->getForumCode($websiteId));
    }

    /**
     * Test secret API key
     */
    public function testGetSecretKey()
    {
        $websiteId = 1;
        $secretKey = 'secretkey';
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(DisqusConfig::XML_PATH_DISQUS_SECRET_KEY, ScopeInterface::SCOPE_WEBSITE, $websiteId)
            ->willReturn($secretKey);
        $this->assertEquals($secretKey, $this->configModel->getSecretKey($websiteId));
    }
}
