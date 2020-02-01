<?php

namespace Unirgy\RapidFlow\Model\ResourceModel;
/** @var \Unirgy\RapidFlow\Helper\Data $hlp */
$hlp = \Magento\Framework\App\ObjectManager::getInstance()->get('\Unirgy\RapidFlow\Helper\Data');
if ($hlp->compareMageVer('2.2.0-dev',null,'<')) {
    abstract class AbstractResource extends AbstractResourceBase
    {
        public function getMyConnection()
        {
            return $this->getConnection();
        }

        protected function getConnection()
        {
            return $this->_db->getConnection();
        }
    }
} else {
    abstract class AbstractResource extends AbstractResourceBase
    {
        public function getMyConnection() {
            return $this->getConnection();
        }

        public function getConnection() {
            return $this->_db->getConnection();
        }
    }
}