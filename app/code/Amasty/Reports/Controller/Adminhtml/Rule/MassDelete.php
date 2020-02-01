<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Rule;

use Amasty\Reports\Api\Data\RuleInterface;

/**
 * Class MassDelete
 * @package Amasty\Reports\Controller\Adminhtml\Rule
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @param RuleInterface $rule
     */
    protected function itemAction(RuleInterface $rule)
    {
        $this->repository->deleteById($rule->getEntityId());
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getErrorMessage()
    {
        return __('We can\'t delete item right now. Please review the log and try again.');
    }

    /**
     * @param int $collectionSize
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($collectionSize = 0)
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been deleted.', $collectionSize);
        }

        return __('No records have been deleted.');
    }
}
