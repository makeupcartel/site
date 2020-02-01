<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Framework\Db\Adapter\Pdo\Mysql;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\Profile as ProfileResource;

class Fixissues extends AbstractProfile
{
    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    public function execute()
    {
        $issueId = $this->getRequest()->getParam('id');
        switch ($issueId) {
            case '1':
                $fixed = $this->_fixEavAttributeIssue();
                break;
            case '2':
                $fixed = $this->_fixWebsitePriceInGlobalScope();
                break;
            default :
                $fixed = false;
                break;
        }

        if ($fixed) {
            $message = __('The problem has been fixed');
        } else {
            $message = __('The problem could not be fixed');
        }

        $this->messageManager->addNoticeMessage($message);
        $this->_forward('index');

        //var_dump($issueId);
        //die;
    }


    protected function _fixEavAttributeIssue()
    {
        /** @var ProfileResource $resource */
        $resource = $this->_profileResource;
        /** @var Mysql $conn */
        $conn = $resource->getConnection();
        try {
            $conn->exec("UPDATE {$resource->getTable('eav_attribute')} SET attribute_model=null WHERE attribute_model=''");
        } catch (\Zend_Db_Adapter_Exception $e) {
            $this->_logger->debug($e->getMessage());

            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function _fixWebsitePriceInGlobalScope()
    {
        if ($this->_catalogHelperData->isPriceGlobal()) {
            /** @var ProfileResource $resource */
            $resource = $this->_profileResource;
            /** @var Mysql $conn */
            $conn = $resource->getConnection();
            $delAttrIdsSel = $conn->select()
                ->from(array('a' => $resource->getTable('eav_attribute')), array('attribute_id'))
                ->join(array('e' => $resource->getTable('eav_entity_type')), 'e.entity_type_id=a.entity_type_id',
                       array())
                ->where("e.entity_type_code='catalog_product'")
                ->where("a.backend_model='catalog/product_attribute_backend_price'");

            $delAttrValuesSql = sprintf('DELETE FROM %s WHERE store_id!=0 AND attribute_id IN (%s)',
                                        $resource->getTable('catalog_product_entity') . '_decimal',
                                        $delAttrIdsSel
            );
            try {
                $conn->exec($delAttrValuesSql);
            } catch (\Zend_Db_Adapter_Exception $e) {
                $this->_logger->debug($e->getMessage());
                return false;
            }
        }
        return true;
    }
}
