<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Block\Adminhtml\Rule\Edit;

use Amasty\Groupcat\Controller\RegistryConstants;
use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton as CatalogRuleGenericButton;

class GenericButton extends CatalogRuleGenericButton
{
    /**
     * Return the current Catalog Rule Id.
     *
     * @return int|null
     */
    public function getRuleId()
    {
        $groupcatRule = $this->registry->registry(RegistryConstants::CURRENT_GROUPCAT_RULE_ID);
        return $groupcatRule ? $groupcatRule->getId() : null;
    }
}
