<?php

namespace Convert\CustomerApproval\Helper;

use Bss\CustomerApproval\Helper\Data as BssData;

/**
 * Class Data
 *
 * @package Convert\CustomerApproval\Helper
 */
class Data extends BssData
{
    /**
     * Check if the approve email should be force sent
     *
     * @return bool
     */
    public function isForceApproveSendEnable()
    {
        return $this->scopeConfig->isSetFlag(
            'customer_approval/email_setting/send_approve',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
