<?php

namespace Unirgy\RapidFlow\Model;

class UrlRewriteDbStorage extends \Magento\UrlRewrite\Model\Storage\DbStorage
{
    public function deleteByData(array $data)
    {
        $table = $this->resource->getTableName(self::TABLE_NAME);
        $where = [];
        foreach ($data as $column => $value) {
            $columnSql = $this->connection->quoteIdentifier($column);
            $valueSql = $this->connection->quote($value);
            $where[] = sprintf('%s IN (%s)', $columnSql, $valueSql);
        }
        $query = sprintf("DELETE FROM %s WHERE %s",
            $table, implode(' and ', $where)
        );
        $this->connection->query($query);
        /*
        $this->connection->query(
            $this->prepareSelect($data)->deleteFromSelect($this->resource->getTableName(self::TABLE_NAME))
        );
        */
    }
    protected function insertMultiple($data)
    {
        $newData = [];
        foreach ($data as $d) {
            if (!empty($d['request_path']) || !in_array($d['entity_type'], ['category','product'])) {
                $newData[] = $d;
            }
        }
        return parent::insertMultiple($newData);
    }
}