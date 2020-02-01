<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Setup;

use Amasty\Xsearch\Model\Indexer\Category\Fulltext;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Indexer\Model\Indexer
     */
    private $indexer;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    public function __construct(
        \Magento\Indexer\Model\Indexer $indexer,
        \Magento\Framework\App\State $appState
    ) {
        $this->indexer = $indexer;
        $this->appState = $appState;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            [$this, 'installCallback'],
            [$setup, $context]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function installCallback(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $indexer = $this->indexer->load(Fulltext::INDEXER_ID);
            $indexer->reindexAll();
        } catch (\Exception $ex) {
            //skip reindex error during installation process
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('Please re-run static files deployment and execute setup:upgrade one more time');
        }
    }
}
