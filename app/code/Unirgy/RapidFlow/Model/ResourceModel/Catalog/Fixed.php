<?php

namespace Unirgy\RapidFlow\Model\ResourceModel\Catalog;

use Magento\Framework\Exception\LocalizedException;
use Unirgy\RapidFlow\Model\ResourceModel\AbstractResource\Fixed as AbstractResourceFixed;

class Fixed
    extends AbstractResourceFixed
{

    protected function _importFetchNewDataIds()
    {
        $fieldValues = [];
        foreach ($this->_newRows as $lineNum => $row) {
            $cmd = $row[0][0];
            $rowType = $cmd === '+' || $cmd === '-' || $cmd === '%' ? substr($row[0], 1) : $row[0];
            if (empty($this->_rowTypeFields[$rowType]['columns'])) {
                continue;
            }
            foreach ($this->_rowTypeFields[$rowType]['columns'] as $fieldName => $fieldNode) {
                $col = (int)$fieldNode->col;
                if (!empty($row[$col])) {
                    $fieldValues[$fieldName][$lineNum] = $row[$col];
                }
            }
        }
        $skus = !empty($fieldValues['sku']) ? $fieldValues['sku'] : [];
        if (!empty($fieldValues['linked_sku'])) {
            $skus = array_merge($skus, $fieldValues['linked_sku']);
        }
        if (!empty($fieldValues['selection_sku'])) {
            $skus = array_merge($skus, $fieldValues['selection_sku']);
        }
        if (!empty($skus)) {
            if (count($this->_skus) > $this->_maxCacheItems['sku']) {
                $this->_skus = [];
            }
//            $select = "SELECT entity_id, sku FROM {$this->_t('catalog_product_entity')} WHERE sku IN (" . implode(',', $skus1) . ')';
            $columns = [$this->_entityIdField, 'sku'];
            $useSequence = $this->_rapidFlowHelper->hasMageFeature(self::ROW_ID);
            if ($useSequence) { // if is enterprise, the sequence id = entity_id and is used in some tables
                $columns[] = 'entity_id';
            }

            $select = $this->_write->select()->from($this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY), $columns)
                ->where('sku IN (?)', $skus);
            $rows = $this->_read->fetchAll($select);
            foreach ($rows as $r) {
                $this->_skus[$r['sku']] = $r[$this->_entityIdField];
                if($useSequence){
                    $this->_skuSeq[$r['sku']] = $r['entity_id'];
                }
            }
        }
    }

    protected function _getIdBySku($sku)
    {
        if (empty($this->_skus[$sku])) {
            throw new LocalizedException(__('Invalid SKU (%1)', $sku));
        }
        return $this->_skus[$sku];
    }

    protected function _getSeqIdBySku($sku)
    {
        if (!$this->_rapidFlowHelper->hasMageFeature(self::ROW_ID)) {
            return $this->_getIdBySku($sku);
        }
        if (empty($this->_skuSeq[$sku])) {
            throw new LocalizedException(__('Invalid SKU (%1)', $sku));
        }
        return $this->_skuSeq[$sku];
    }

    protected function _importProcessNewData()
    {
        parent::_importProcessNewData();

        $this->_importFetchNewDataIds();
    }
}
