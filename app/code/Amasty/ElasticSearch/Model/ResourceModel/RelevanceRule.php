<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\ResourceModel;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;

class RelevanceRule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init(RelevanceRuleInterface::TABLE_NAME, RelevanceRuleInterface::RULE_ID);
    }
}
