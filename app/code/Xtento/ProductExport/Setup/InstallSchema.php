<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-05-31T15:05:01+00:00
 * File:          app/code/Xtento/ProductExport/Setup/InstallSchema.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    // @codingStandardsIgnoreStart
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // @codingStandardsIgnoreEnd
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'xtento_productexport_destination'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtento_productexport_destination')
        )->addColumn(
            'destination_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Type'
        )->addColumn(
            'hostname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Hostname'
        )->addColumn(
            'port',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            6,
            ['nullable' => true],
            'Port'
        )->addColumn(
            'username',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Username'
        )->addColumn(
            'password',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Password'
        )->addColumn(
            'timeout',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['nullable' => false, 'default' => 15],
            'Timeout'
        )->addColumn(
            'path',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Path'
        )->addColumn(
            'ftp_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => false],
            'FTP Server Type'
        )->addColumn(
            'ftp_pasv',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'FTP Use Passive Mode'
        )->addColumn(
            'email_sender',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'E-Mail Sender'
        )->addColumn(
            'email_recipient',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'E-Mail Recipient'
        )->addColumn(
            'email_subject',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'E-Mail Subject'
        )->addColumn(
            'email_body',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16777215,
            ['nullable' => false],
            'E-Mail Body'
        )->addColumn(
            'email_html',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'E-Mail HTML'
        )->addColumn(
            'email_attach_files',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'E-Mail Attach Files'
        )->addColumn(
            'custom_class',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Custom Class'
        )->addColumn(
            'custom_function',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Custom Function'
        )->addColumn(
            'last_result',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Last Result'
        )->addColumn(
            'last_result_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Last Result Message'
        )->addColumn(
            'last_modification',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Last Modification'
        )->setComment(
            'Xtento_ProductExport Destination table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'xtento_productexport_log'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtento_productexport_log')
        )->addColumn(
            'log_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Profile ID'
        )->addColumn(
            'files',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16777215,
            ['nullable' => false],
            'Exported Files'
        )->addColumn(
            'destination_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Destination IDs'
        )->addColumn(
            'export_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            5,
            ['nullable' => false],
            'Export Type (ID)'
        )->addColumn(
            'export_event',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Export Event'
        )->addColumn(
            'records_exported',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            8,
            ['nullable' => false],
            'Records Exported'
        )->addColumn(
            'result',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            2,
            ['nullable' => false],
            'Export Result'
        )->addColumn(
            'result_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16777215,
            ['nullable' => false],
            'Export Result Message'
        )->addIndex(
            $installer->getIdxName(
                'xtento_productexport_log',
                ['profile_id', 'created_at'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['profile_id', 'created_at'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
        )->setComment(
            'Xtento_ProductExport Log table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'xtento_productexport_profile'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtento_productexport_profile')
        )->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'entity',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Export Entity'
        )->addColumn(
            'enabled',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Profile Enabled'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Profile Name'
        )->addColumn(
            'destination_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Destination IDs'
        )->addColumn(
            'last_execution',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Last Execution'
        )->addColumn(
            'last_modification',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Last Modification'
        )->addColumn(
            'conditions_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16777215,
            ['nullable' => false],
            'Conditions / Filters'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false],
            'Store ID'
        )->addColumn(
            'export_one_file_per_object',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Export one file per object'
        )->addColumn(
            'export_filter_new_only',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Export filter: New objects only'
        )->addColumn(
            'export_filter_datefrom',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            ['nullable' => true],
            'Export filter: Date from'
        )->addColumn(
            'export_filter_dateto',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            ['nullable' => true],
            'Export filter: Date to'
        )->addColumn(
            'export_filter_older_x_minutes',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => true],
            'Export filter: Older than X minutes'
        )->addColumn(
            'export_filter_last_x_days',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => true],
            'Export filter: Last X days only'
        )->addColumn(
            'export_filter_updated_last_x_minutes',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => true],
            'Export filter: Updated during last X minutes'
        )->addColumn(
            'export_filter_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Export filter: Status'
        )->addColumn(
            'export_filter_product_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Export filter: Product type'
        )->addColumn(
            'export_filter_product_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Export filter: Product Status'
        )->addColumn(
            'export_filter_product_visibility',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Export filter: Product Visibility'
        )->addColumn(
            'export_filter_instock_only',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Export filter: In Stock only'
        )->addColumn(
            'save_files_manual_export',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 1],
            'Save files on destinations for manual exports'
        )->addColumn(
            'export_empty_files',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Export empty files'
        )->addColumn(
            'customer_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'default' => 0],
            'The customer group ID which prices will be fetched for'
        )->addColumn(
            'export_replace_nl_br',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 1],
            'Replace newline with br HTML tag'
        )->addColumn(
            'export_url_remove_store',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Remove store view code from URL'
        )->addColumn(
            'export_strip_tags',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Strip HTML tags from export'
        )->addColumn(
            'manual_export_enabled',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 1],
            'Manual export enabled'
        )->addColumn(
            'start_download_manual_export',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 1],
            'Start download after manual export'
        )->addColumn(
            'save_files_local_copy',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 1],
            'Save local copies of exports'
        )->addColumn(
            'event_observers',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Events observed'
        )->addColumn(
            'cronjob_enabled',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Cronjob export enabled'
        )->addColumn(
            'cronjob_frequency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Cronjob frequency'
        )->addColumn(
            'cronjob_custom_frequency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Cronjob custom frequency expression'
        )->addColumn(
            'output_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'xsl'],
            'Export output generator'
        )->addColumn(
            'attributes_to_select',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Performance Settings: Specific attributes'
        )->addColumn(
            'filename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Export filename (only All-fields-in-XML)'
        )->addColumn(
            'encoding',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Export file encoding (only All-fields-in-XML)'
        )->addColumn(
            'xsl_template',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16777215,
            ['nullable' => false],
            'XSL Template'
        )->addColumn(
            'test_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Test export IDs'
        )->setComment(
            'Xtento_ProductExport Profile table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'xtento_productexport_profile_history'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtento_productexport_profile_history')
        )->addColumn(
            'history_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Profile ID'
        )->addColumn(
            'log_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Log ID'
        )->addColumn(
            'entity',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Export Entity'
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Exported entity ID'
        )->addColumn(
            'exported_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Exported At'
        )->addIndex(
            $installer->getIdxName(
                'xtento_productexport_profile_history',
                ['entity', 'entity_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['entity', 'entity_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $installer->getIdxName(
                'xtento_productexport_profile_history',
                ['profile_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['profile_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
        )->setComment(
            'Xtento_ProductExport Log table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
