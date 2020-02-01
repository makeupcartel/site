<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerApproval
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerApproval\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Options extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('eav_attribute_option_value', 'value_id');
    }

    /**
     * @param $value
     * @return mixed
     * @throws \Zend_Db_Statement_Exception
     */
    public function getStatusValue($value)
    {
        $connection = $this->getConnection();
        $select = $this->getConnection()->select()
            ->from(
                $this->getTable('eav_attribute_option_value'),
                'option_id'
            )->where('value = ?', $value);

        $data = $connection->query($select);

        while ($row = $data->fetch()) {
            $value = $row;
        }
        
        return $value;
    }
}
