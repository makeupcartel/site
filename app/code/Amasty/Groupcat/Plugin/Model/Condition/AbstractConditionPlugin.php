<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Plugin\Model\Condition;

class AbstractConditionPlugin
{
    /**
     * fix Magento issue - validate by multiselect
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $subject
     * @param array|string|int|float                          $result
     *
     * @return array|string|int|float
     */
    public function afterGetValueParsed(\Magento\Rule\Model\Condition\AbstractCondition $subject, $result)
    {
        $value = $subject->getData('value');
        if ($subject->isArrayOperatorType() && is_array($value)) {
            return $value;
        }

        return $result;
    }
}
