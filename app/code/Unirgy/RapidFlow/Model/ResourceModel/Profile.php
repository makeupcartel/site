<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Profile extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('urapidflow_profile', 'profile_id');
    }

    public function sync(AbstractModel $object, $saveFields=null, $loadFields=null)
    {
        $conn = $this->getConnection();
        $table = $this->getMainTable();

        $condition = $conn->quoteInto($this->getIdFieldName().'=?', $object->getId());

        if ($saveFields) {
            $saveData = [];
            foreach ($saveFields as $k) {
                $saveData[$k] = $object->getData($k);
            }
            $conn->update($table, $saveData, $condition);
        }

        if ($loadFields) {
            $loadData = $conn->fetchRow($conn->select()->from($table, $loadFields)->where($condition));
            foreach ($loadData as $k=>$v) {
                $object->setData($k, $v);
            }
        }

        return $this;
    }

    public function getResources()
    {
        return $this->_resources;
    }
}
