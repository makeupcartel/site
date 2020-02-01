<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Sampleimport;

use Magento\Framework\Xml\Parser;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\BlockFactory as BlockFactory;
use Magento\Framework\App\ResourceConnection;
use Magedelight\Megamenu\Model\MenuFactory;
use Magedelight\Megamenu\Model\MenuItemsFactory;

class Import extends \Magento\Backend\App\Action
{

    public $importPath;
    public $parser;
    public $blockRepository;
    public $blockFactory;
    public $resource;
    public $menuFactory;
    public $menuItemFactory;

    /**
     * @var
     */
    private $moduleReader;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\File\Csv $fileCsv
     * @param \Magedelight\GeoIp\Model\ImportFactory $importFactory
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Parser $parser,
        BlockRepositoryInterface $blockRepository,
        BlockFactory $blockFactory,
        ResourceConnection $resource,
        MenuFactory $menuFactory,
        MenuItemsFactory $menuItemFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        parent::__construct($context);
        $this->moduleReader = $moduleReader;

        $etcDir = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
            'Magedelight_Megamenu'
        );

        $this->importPath = $etcDir . '/import/';

        $this->parser = $parser;
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        $this->resource = $resource;
        $this->menuFactory = $menuFactory;
        $this->menuItemFactory = $menuItemFactory;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Imports country list from csv file
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {



        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $data = $this->getRequest()->getPostValue();
            $this->importCms($data);
        } catch (\Exception $e) {
            $message = $this->messageManager->addError(
                __($e->getMessage())
            );
        }
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

    public function importCms($importdata)
    {
        $xmlPath = $this->importPath . 'import.xml';
        $overwrite = false;

        if ($importdata['override'] == '1') {
            $overwrite = true;
        }
        if (!is_readable($xmlPath)) {
            throw new \Exception(
                __("Can't get the data file for import : " . $xmlPath)
            );
        }
        $data = $this->parser->load($xmlPath)->xmlToArray();

        $conflictingOldItems = [];

        $i = 0;

        foreach ($data['root']['blocks']['item'] as $_item) {
            $exist = false;

            $cms_collection = $this->blockFactory->create()->getCollection()
                    ->addFieldToFilter('identifier', $_item['identifier']);

            if (count($cms_collection) > 0) {
                $exist = true;
            }

            if ($overwrite) {
                if ($exist) {
                    $conflictingOldItems[] = $_item['identifier'];
                    $this->blockRepository->deleteById($_item['identifier']);
                }
            } else {
                if ($exist) {
                    $conflictingOldItems[] = $_item['identifier'];
                    continue;
                }
            }
            $_item['stores'] = [0];
            if (version_compare($this->productMetadata->getVersion(), '2.2.0', '>=') && $_item['identifier'] === 'menudemo-1-column-6-products') {
                $_item['content'] = '{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="5" template="product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^]^]"}}';
            }
            if (version_compare($this->productMetadata->getVersion(), '2.2.0', '>=') && $_item['identifier'] === 'menudemo-1-column-3-products') {
                $_item['content'] = '<div class="product-column-count3"><h4>Hot Product</h4>{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="3" template="product/widget/content/grid.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^]^]"}}</div>';
            }
            $this->blockFactory->create()->setData($_item)->save();
            $i++;
        }

        foreach ($data['root']['menus']['item'] as $_item) {
            $_item['store_id'] = [0];
            $menuid = $_item['menu_id'];
            unset($_item['menu_id']);
            $menu = $this->menuFactory->create()->setData($_item)->save();
            foreach ($data['root']['menu_items']['item'] as $sub_item) {
                if ($menuid == $sub_item['menu_id']) {
                    $sub_item['menu_id'] = $menu->getMenuId();
                    $this->menuItemFactory->create()->setData($sub_item)->save();
                }
            }
            $i++;
        }

        $message = "";
        if ($i) {
            $this->messageManager->addSuccess(__($i . " item(s) was(were) imported."));
        } else {
            $this->messageManager->addError(__("No items were imported."));
        }

        if ($overwrite) {
            if ($conflictingOldItems) {
                $message .= "Items (" . count($conflictingOldItems) . ") with the following identifiers were overwritten:<br/>" . implode('<br> ', $conflictingOldItems);
                $this->messageManager->addNotice(__($message));
            }
        } else {
            if ($conflictingOldItems) {
                $message .= "<br/>Unable to import items (" . count($conflictingOldItems) . ") with the following identifiers (they already exist in the database):<br/>" . implode(', ', $conflictingOldItems);
                $this->messageManager->addNotice(__($message));
            }
        }
    }
}
