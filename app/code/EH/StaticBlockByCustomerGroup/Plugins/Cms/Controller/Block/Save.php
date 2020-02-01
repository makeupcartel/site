<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2019 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_StaticBlockByCustomerGroup
 */
 
namespace EH\StaticBlockByCustomerGroup\Plugins\Cms\Controller\Block;

use Magento\Framework\App\Action\AbstractAction;

/**
 * Cms block save before
 */
class Save
{
    /**
     * Before execute
     *
     * @param AbstractAction $request
     */
    public function beforeExecute(AbstractAction $request)
    {
        $customerGroupIds = $request->getRequest()->getPostValue('customer_group_ids');
        if ($customerGroupIds == '' || empty($customerGroupIds)) {
            $request->getRequest()->setPostValue('customer_group_ids', null);
        } elseif (is_array($customerGroupIds)) {
            $customerGroupIds = implode(',', $customerGroupIds);
            $request->getRequest()->setPostValue('customer_group_ids', $customerGroupIds);
        }
    }
}
