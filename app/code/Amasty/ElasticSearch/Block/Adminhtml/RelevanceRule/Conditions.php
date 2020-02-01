<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Block\Adminhtml\RelevanceRule;

use Amasty\ElasticSearch\Controller\Adminhtml\RelevanceRule\AbstractRelevance;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class Conditions extends Generic implements TabInterface
{
    /**
     * @var Form\Renderer\Fieldset
     */
    private $rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditions;

    /**
     * @var \Amasty\Elasticsearch\Model\RelevanceRuleRepository
     */
    private $relevanceRuleRepository;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Amasty\Elasticsearch\Model\RelevanceRuleRepository $relevanceRuleRepository,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->conditions = $conditions;
        $this->rendererFieldset = $rendererFieldset;
        $this->relevanceRuleRepository = $relevanceRuleRepository;
    }

    /**
     * @inheritdoc
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry(AbstractRelevance::CURRENT_RULE)->getCatalogRule();
        $form = $this->addTabToForm($model);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @param \Magento\CatalogRule\Model\Rule $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addTabToForm(
        \Magento\CatalogRule\Model\Rule $model,
        $fieldsetId = 'conditions_fieldset',
        $formName = 'amasty_elastic_relevancerule_form'
    ) {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);
        $newChildUrl = $this->getUrl(
            'amasty_elastic/relevancerule/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );

        $renderer = $this->rendererFieldset
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset($fieldsetId,
            ['legend' => __('Rule won\'t work if no conditions are applied')])
            ->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true
            ]
        )->setRule($model)->setRenderer($this->conditions);

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName, $conditionsFieldSetId);
        return $form;
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     * @param string $jsFormName
     * @return void
     */
    private function setConditionFormName(
        \Magento\Rule\Model\Condition\AbstractCondition $conditions,
        $formName,
        $jsFormName
    ) {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
