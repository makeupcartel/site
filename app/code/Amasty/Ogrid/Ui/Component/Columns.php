<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Ui\Component;

use Magento\Ui\Component\Container;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Framework\Api\FilterBuilder;
use Amasty\Ogrid\Helper\Data as Helper;

class Columns extends Container
{
    /**
     * @var BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $typeToFilter = [
        'text' => 'text',
        'select' => 'text',
        'multiselect' => 'text'
    ];

    /**
     * Columns constructor.
     * @param ContextInterface $context
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param FilterBuilder $filterBuilder
     * @param Helper $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        BookmarkManagementInterface $bookmarkManagement,
        FilterBuilder $filterBuilder,
        Helper $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->bookmarkManagement = $bookmarkManagement;
        $this->helper = $helper;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $columnsConfiguration = $this->getData('config');

        if (array_key_exists('productColsData', $columnsConfiguration)) {
            $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
                'current',
                'sales_order_grid'
            );
            $config = $bookmark ? $bookmark->getConfig() : null;
            $bookmarksCols = [];
            if (is_array($config) && isset($config['current']) && isset($config['current']['columns'])) {
                $bookmarksCols = $config['current']['columns'];
            }

            foreach ($this->getAttributeCollection() as $attribute) {
                $inputType = $attribute->getFrontendInput();
                $columnConfig = [
                    'visible' => false,
                    'filter' => null,
                    'label' => $attribute->getFrontendLabel(),
                    'productAttribute' => true,
                    'frontendInput' => $inputType
                ];

                if (array_key_exists($inputType, $this->typeToFilter)) {
                    $columnConfig['filter'] = $this->typeToFilter[$inputType];
                }
                $columnsConfiguration['productColsData'][$attribute->getAttributeDbAlias()] = $columnConfig;
            }

            foreach ($columnsConfiguration['productColsData'] as $id => &$config) {
                $config['amogrid'] = [
                    'label' => $config['label'],
                    'title' => '',
                    'visible' => true
                ];

                if (isset($bookmarksCols[$id]) && isset($bookmarksCols[$id]['amogrid_title'])) {
                    $config['amogrid']['title'] = $bookmarksCols[$id]['amogrid_title'];
                } elseif (isset($config['label'])) {
                    $config['amogrid']['title'] = $config['label'];
                }

                if (isset($bookmarksCols[$id]) && isset($bookmarksCols[$id]['visible'])) {
                    $config['amogrid']['visible'] = $bookmarksCols[$id]['visible'];
                } elseif (isset($config['visible'])) {
                    $config['amogrid']['visible'] = $config['visible'];
                }

                $config['label'] = $config['amogrid']['title'];
            }

            $this->setData('config', $columnsConfiguration);
        }
    }

    /**
     * @return \Amasty\Ogrid\Model\ResourceModel\Attribute\Collection
     */
    public function getAttributeCollection()
    {
        return $this->helper->getAttributeCollection();
    }
}
