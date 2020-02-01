<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Groupcat\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    use GroupcatTableInitTrate;

    /**
     * @since 1.2.0 table relations has moved to separate tables
     * @since 1.0.0 init
     *
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createGroupcatRuleTable($setup);
        /* relation tables creates is in UpgradeSchema */

        $setup->endSetup();
    }
}
