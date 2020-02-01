<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Message;

use Amasty\ElasticSearch\Model\Config;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\ObjectManagerInterface;

class HardcodedEngine implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function getIdentity()
    {
        return md5('HARDCODED_SEARCHENGINE');
    }

    /**
     * @inheritdoc
     */
    public function isDisplayed()
    {
        $result = false;
        if (class_exists(ConfigSourceInterface::class)) {
            $configSource = $this->objectManager->get(ConfigSourceInterface::class);
            $hardcodedEngine = $configSource->get('default/' . EngineProvider::CONFIG_ENGINE_PATH);
            $result = !!$hardcodedEngine && $hardcodedEngine !== Config::ELASTIC_SEARCH_ENGINE;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getText()
    {
        return __(
            'Amasty Elastic is not working because "%1" search engine is set in app/etc/env.php file.',
            $this->getHardcodedEngine()
        );
    }

    /**
     * @return string
     */
    private function getHardcodedEngine()
    {
        $hardcodedEngine = __('Default')->render();
        if (class_exists(ConfigSourceInterface::class)) {
            $configSource = $this->objectManager->get(ConfigSourceInterface::class);
            $hardcodedEngine = $configSource->get('default/' . EngineProvider::CONFIG_ENGINE_PATH);
        }

        return $hardcodedEngine;
    }

    /**
     * @inheritdoc
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}
