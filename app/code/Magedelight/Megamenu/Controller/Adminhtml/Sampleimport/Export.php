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

use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magedelight\Megamenu\Model\MenuItemsFactory;
use Magento\Cms\Model\BlockFactory;

class Export extends \Magento\Backend\App\Action
{
    
    public $filter;
    
    /**
     * @var WriteInterface
     */
    protected $directory;
    
    /**
     * @var SearchResultIteratorFactory
     */
    protected $iteratorFactory;
    
    /**
     * @var FileFactory
     */
    protected $fileFactory;
    
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;
    
    protected $staticBlocks;
    
    protected $staticBlocksStore = [];
    
    protected $menus;
    
    protected $menuItems;
    
    protected $menuItemsFactory;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        SearchResultIteratorFactory $iteratorFactory,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        MenuItemsFactory $menuItems,
        BlockFactory $blockFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->iteratorFactory = $iteratorFactory;
        $this->fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->menuItemsFactory = $menuItems;
        $this->_blockFactory = $blockFactory;
    }
    
    /**
     * Imports country list from csv file
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        
        return $this->fileFactory->create('export.xml', $this->getXmlFile(), 'var');
    }
    
    public function getXmlFile()
    {
        $name = md5(microtime());
        $component = $this->filter->getComponent();
        $file = 'export/'. $component->getName() . $name . '.xml';
        
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();

        $component->getContext()->getDataProvider()->setLimit(0, 0);

        /** @var SearchResultInterface $searchResult */
        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();

        /** @var DocumentInterface[] $searchResultItems */
        $searchResultItems = $searchResult->getItems();
        
        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItems]);
        
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->write('<root>');
        $this->menus = '<menus>';
        $this->menuItems = '<menu_items>';
        $this->staticBlocks = '<blocks>';
        
        foreach ($searchResultIterator as $single) {
            $this->createMenus($single);
        }
            
        $this->menus .= '</menus>';
        $this->menuItems .= '</menu_items>';
        $this->staticBlocks .= '</blocks>';
        
        $stream->write($this->menus);
        $stream->write($this->menuItems);
        $stream->write($this->staticBlocks);
        $stream->write('</root>');
        $stream->unlock();
        $stream->close();
        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
    
    public function createMenus($menu)
    {
        if (!empty($menu->getData())) {
            $this->menus .= '<item>';
            $this->menus .= '<menu_id>'.$menu->getMenuId().'</menu_id>';
            $this->menus .= '<menu_name>'.$menu->getMenuName().'</menu_name>';
            $this->menus .= '<menu_design_type>'.$menu->getMenuDesignType().'</menu_design_type>';
            $this->menus .= '<menu_style><![CDATA['.$menu->getMenuStyle().']]></menu_style>';
            $this->menus .= '<is_active>'.$menu->getIsActive().'</is_active>';
            $this->menus .= '<menu_type>'.$menu->getMenuType().'</menu_type>';
            $this->menus .= '<customer_groups>'.$menu->getCustomerGroups().'</customer_groups>';
            $this->menus .= '</item>';
            $this->createMenuItems($menu);
        }
    }
    
    public function createMenuItems($menu)
    {
        $menuItems = $this->menuItemsFactory->create()->getCollection()
                ->addFieldToFilter('menu_id', $menu->getMenuId())
                ->addFieldToFilter('item_parent_id', 0)
                ->setOrder('sort_order', 'ASC');
        foreach ($menuItems as $singlrMenuItem) {
            if (!empty($singlrMenuItem->getData())) {
                $this->menuItems .= '<item>';
                $this->menuItems .= '<item_name>'.$singlrMenuItem->getItemName().'</item_name>';
                $this->menuItems .= '<item_type>'.$singlrMenuItem->getItemType().'</item_type>';
                $this->menuItems .= '<sort_order>'.$singlrMenuItem->getSortOrder().'</sort_order>';
                $this->menuItems .= '<item_parent_id>'.$singlrMenuItem->getItemParentId().'</item_parent_id>';
                $this->menuItems .= '<menu_id>'.$singlrMenuItem->getMenuId().'</menu_id>';
                $this->menuItems .= '<object_id>'.$singlrMenuItem->getObjectId().'</object_id>';
                $this->menuItems .= '<item_link>'.$singlrMenuItem->getItemLink().'</item_link>';
                $this->menuItems .= '<item_columns>'.$singlrMenuItem->getItemColumns().'</item_columns>';
                $this->menuItems .= '<item_font_icon><![CDATA['.$singlrMenuItem->getItemFontIcon().']]></item_font_icon>';
                $this->menuItems .= '<item_class>'.$singlrMenuItem->getItemClass().'</item_class>';
                $this->menuItems .= '<animation_option>'.$singlrMenuItem->getAnimationOption().'</animation_option>';
                $this->menuItems .= '</item>';
                if (!empty($singlrMenuItem->getItemColumns())) {
                    $columns = json_decode($singlrMenuItem->getItemColumns());
                    $totalColumn = count($columns);
                    for ($i = 0; $i < $totalColumn; $i++) {
                        $type = $columns[$i]->type;
                        if ($type == 'block') {
                            $subBlockId = $columns[$i]->value;
                            $this->createStaticBlocks($subBlockId);
                        }
                    }
                }
            }
        }
    }
    
    public function createStaticBlocks($identifier)
    {
        if (!in_array($identifier, $this->staticBlocksStore)) {
            $block = $this->_blockFactory->create()->load($identifier);
            $this->staticBlocks .= '<item>';
            $this->staticBlocks .= '<title>'.$block->getTitle().'</title>';
            $this->staticBlocks .= '<identifier>'.$block->getIdentifier().'</identifier>';
            $this->staticBlocks .= '<content><![CDATA['.$block->getContent().']]></content>';
            $this->staticBlocks .= '<is_active>'.$block->getIsActive().'</is_active>';
            $this->staticBlocks .= '</item>';
            $this->staticBlocksStore[] = $identifier;
        }
    }
}
