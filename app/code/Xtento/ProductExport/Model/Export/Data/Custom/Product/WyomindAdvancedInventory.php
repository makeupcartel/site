<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-06-11T21:23:36+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Data/Custom/Product/WyomindAdvancedInventory.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Data\Custom\Product;

use Xtento\ProductExport\Model\Export;

class WyomindAdvancedInventory extends \Xtento\ProductExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * WyomindAdvancedInventory constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->objectManager = $objectManager;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'Wyomind_AdvancedInventory Data Export',
            'category' => 'Order',
            'description' => 'Export stock per warehouse',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_PRODUCT],
            'third_party' => true,
            'depends_module' => 'Wyomind_AdvancedInventory',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];

        // Fetch fields to export
        $product = $collectionItem->getProduct();

        if ($this->fieldLoadingRequired('wyomind_advancedinventory_warehouses')) {
            try {
                $this->writeArray = &$returnArray['wyomind_advancedinventory_warehouses']; // Write on "wyomind_advancedinventory_warehouses" level

                $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
                $readAdapter = $resource->getConnection();
                $table = $resource->getTableName('advancedinventory_stock');
                $placeTable = $resource->getTableName('pointofsale');

                $binds = [
                    'productId' => $product->getId(),
                ];
                $dataRows = $readAdapter->fetchAll("SELECT * FROM {$table} WHERE product_id = :productId", $binds);

                if (is_array($dataRows)) {
                    foreach ($dataRows as $dataRow) {
                        $this->writeArray = &$returnArray['wyomind_advancedinventory_warehouses'][];
                        foreach ($dataRow as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                        $placeId = $dataRow['place_id'];
                        if ($placeId) {
                            $binds = [
                                'placeId' => $placeId
                            ];
                            $dataRowPlace = $readAdapter->fetchRow("SELECT * FROM {$placeTable} WHERE place_id = :placeId", $binds);
                            foreach ($dataRowPlace as $key => $value) {
                                $this->writeValue($key, $value);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {

            }
            $this->writeArray = &$returnArray;
        }

        // Done
        return $returnArray;
    }
}