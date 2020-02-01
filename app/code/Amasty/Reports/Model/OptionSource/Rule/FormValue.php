<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\OptionSource\Rule;

use Amasty\Reports\Api\Data\RuleInterface;
use Amasty\Reports\Api\RuleRepositoryInterface;
use Amasty\Reports\Model\OptionSource\Rule\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class FormValue
 * @package Amasty\Reports\Model\OptionSource\Rule
 */
class FormValue implements ArrayInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options[] = [
            'value' => '',
            'label' => __('All results')
        ];
        
        $searchCriteria = $this->searchCriteriaBuilder->create();
        /** @var RuleInterface $rule */
        foreach ($this->ruleRepository->getList($searchCriteria)->getItems() as $rule) {
            $title = $rule->getTitle();
            if ($rule->getStatus() == Status::PROCESSING) {
                $title .= ' (' . __('Processing') . ')';
            }
            $options[] = [
                'value' => $rule->getEntityId(),
                'label' => $title
            ];
        }

        return $options;
    }
}
