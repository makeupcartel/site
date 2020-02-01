<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */

namespace Amasty\Oaction\Controller\Adminhtml\Massaction;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends \Amasty\Oaction\Controller\Adminhtml\Massaction
{
    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function massAction(AbstractCollection $collection)
    {
        $action  = $this->getRequest()->getParam('type');
        $param   = (int) $this->getRequest()->getParam('notify');
        $oaction = $this->getRequest()->getParam('oaction');
        if (!$param) {
            $param  = $this->getRequest()->getParam('status');
        }

        try {
            $className = 'Amasty\Oaction\Model\Command\\'  . ucfirst($action);
            if (class_exists($className)) {
                $command = $this->_objectManager->create($className);
                $success = $command->execute($collection, $param, $oaction);

                if ($success) {
                    $this->messageManager->addSuccess($success);
                }

                // show non critical erroes to the user
                foreach ($command->getErrors() as $err) {
                    $this->messageManager->addError($err);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        catch (\Amasty\Oaction\Model\CustomException $e) {
            $this->messageManager->addException($e, $e->getMessage());
        }
        catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while updating the product(s) data.'));
        }

        if ($command && $command->hasResponse()) {
            return $this->_fileFactory->create(
                $command->getResponseName(),
                $command->getResponseBody(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/');
            return $resultRedirect;
        }

    }
}
