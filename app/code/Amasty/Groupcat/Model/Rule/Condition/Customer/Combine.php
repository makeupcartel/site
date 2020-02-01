<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Model\Rule\Condition\Customer;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Amasty\RulesPro\Model\Rule\Condition\CustomerFactory
     */
    private $conditionCustomerFactory;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\Groupcat\Model\Rule\Condition\CustomerFactory $conditionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->conditionCustomerFactory = $conditionFactory;
        $this->setType('Amasty\Groupcat\Model\Rule\Condition\Customer\Combine');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $options = parent::getNewChildSelectOptions();

        /** @var \Amasty\RulesPro\Model\Rule\Condition\Customer $condition */
        $condition = $this->conditionCustomerFactory->create();
        $conditionAttributes = $condition->loadAttributeOptions()->getAttributeOption();

        $options[] = [
            'value' => 'Amasty\Groupcat\Model\Rule\Condition\Customer\Combine',
            'label' => __('Conditions Combination'),
        ];
        $attributes = [];
        foreach ($conditionAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Amasty\Groupcat\Model\Rule\Condition\Customer' . '|' . $code,
                'label' => $label,
            ];
        }
        $options[] = [
            'value' => $attributes,
            'label' => __('Customer attributes'),
        ];

        return $options;
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var \Amasty\Groupcat\Model\Rule\Condition\Customer $condition */
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
