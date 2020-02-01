<?php
namespace Convert\Catalog\Plugin\Category;

class Save
{
    public function beforeExecute(\Magento\Catalog\Controller\Adminhtml\Category\Save $subject)
    {
        if($subject->getRequest()->getPostValue('custom_design_from'))
            $subject->getRequest()->setPostValue('custom_design_from', null);
        if($subject->getRequest()->getPostValue('custom_design_to'))
            $subject->getRequest()->setPostValue('custom_design_to', null);
    }
}