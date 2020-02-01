<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Api;

/**
 * Interface RuleRepositoryInterface
 * @api
 */
interface RuleRepositoryInterface
{
    /**
     * @param \Amasty\Groupcat\Api\Data\RuleInterface $rule
     * @return \Amasty\Groupcat\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Groupcat\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return \Amasty\Groupcat\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($ruleId);

    /**
     * @param \Amasty\Groupcat\Api\Data\RuleInterface $rule
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Groupcat\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($ruleId);
}
