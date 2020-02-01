<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package   Plumrocket_Base
 * @copyright Copyright (c) 2015-2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license   http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Base\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/* Uninstall  */
abstract class AbstractUninstall implements UninstallInterface
{
    /**
     * @var string
     */
    protected $_configSectionId;

    /**
     * @var array
     */
    protected $_attributes = [];

    /**
     * @var array
     */
    protected $_tables = [];

    /**
     * @var array
     */
    protected $_tablesFields = [];

    /**
     * @var array
     */
    protected $_pathes = [];

    /**
     * @var array
     */
    protected $_cmsBlocks = [];

    /**
     * @var \Magento\Framework\Module\Setup\Context
     */
    protected $context;

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetupFactory;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_cmsBlockFactory;

    /**
     * Initialize uninstall model
     *
     * @param \Magento\Framework\Module\Setup\Context $context
     * @param \Magento\Eav\Setup\EavSetupFactory      $eavSetupFactory
     * @param \Magento\Cms\Model\BlockFactory         $cmsBlockFactory
     */
    public function __construct(
        \Magento\Framework\Module\Setup\Context $context,
        \Magento\Eav\Setup\EavSetupFactory      $eavSetupFactory,
        \Magento\Cms\Model\BlockFactory         $cmsBlockFactory
    ) {
        $this->context          = $context;
        $this->eavSetupFactory  = $eavSetupFactory;
        $this->_cmsBlockFactory = $cmsBlockFactory;
    }

    /**
     * Uninstall
     *
     * @param  SchemaSetupInterface   $setup
     * @param  ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $moduleName = $this->getModuleName();
        $dataSetup = new \Magento\Setup\Module\DataSetup($this->context);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $dataSetup]);

        //remove attribute
        foreach($this->_attributes as $entityTypeName => $attributeNames){
            $entityTypeId = $eavSetup->getEntityTypeId($entityTypeName);
            foreach($attributeNames as $attributeName){
                $eavSetup->removeAttribute($entityTypeId, $attributeName);
            }
        }

        //remove tables
        foreach($this->_tables as $_tableName){
            $_tableName = $setup->getTable($_tableName);
            $setup->getConnection()->dropTable($_tableName);
        }

        //remove tables fields
        foreach($this->_tablesFields as $_tableName => $_fields){
            $_tableName = $setup->getTable($_tableName);
            foreach($_fields as $_field){
                try{
                    $setup->getConnection()->dropColumn($_tableName, $_field);
                } catch (\Exception $e) {

                }
            }
        }

        //remove static blocks
        foreach($this->_cmsBlocks as $_identifier){
            $cmsBlock = $this->_cmsBlockFactory->create();
            $cmsBlock->load($_identifier);
            if ($cmsBlock->getId()) {
                $cmsBlock->delete();
            }
        }

        //remove config
        if ($this->_configSectionId) {
            $setup->getConnection()->delete(
                $setup->getTable('core_config_data'),
                ['path LIKE ?' => $this->_configSectionId.'/%']
            );

            $setup->getConnection()->delete(
                $setup->getTable('setup_module'),
                ['module = ?' => $moduleName]
            );
        }

        $confFile = BP . '/app/etc/config.php';
        $config = include $confFile;
        if (isset($config['modules'][$moduleName])) {
            unset($config['modules'][$moduleName]);
            $f = fopen($confFile, 'w');
            fwrite($f, '<?php'."\r\n".'return '.var_export($config, true).';');
            fclose($f);
        }

        exec('rm -rf ' . BP . '/var/cache');

        foreach ($this->_pathes as $path) {
            exec('rm -rf ' . BP . $path);
        }

        $setup->endSetup();
    }


    public function getModuleName()
    {
        $className = get_class($this);
        $namespace = substr(
            $className,
            0,
            strpos($className, '\\' . 'Setup')
        );

        return str_replace('\\', '_', $namespace);
    }
}
