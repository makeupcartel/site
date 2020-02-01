<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Model\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;

class Invoiceship extends \Amasty\Oaction\Model\Command\Invoice
{
    /**
     * Executes the command
     *
     * @param AbstractCollection $collection
     * @param bool $notifyCustomer
     * @return string success message if any
     */
    public function execute(AbstractCollection $collection, $notifyCustomer, $oaction)
    {
        $success = parent::execute($collection, $notifyCustomer, $oaction);
        if ($success) {
            $className = 'Amasty\Oaction\Model\Command\Ship';
            if (class_exists($className)) {
                $command = $this->objectManager->create($className);
                $success .= '<br />' . $command->execute($collection, $notifyCustomer, $oaction);
            }
        }

        return $success;
    }

    protected function _getDefault()
    {
        return (int)$this->helper->getModuleConfig('invoice/notify')
            && (int)$this->helper->getModuleConfig('ship/notify');
    }
}
