<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Magento\Rule\Block\Conditions as BlockConditions;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Rule\Product;
use Aheadworks\Blog\Model\Rule\ProductFactory;
use Magento\Rule\Model\Condition\AbstractCondition as RuleAbstractCondition;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

/**
 * Class Conditions
 * @package Aheadworks\Blog\Block\Adminhtml\Post\Edit\Tab
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Conditions extends Generic implements TabInterface
{
    /**
     * @var string
     */
    const FORM_NAME = 'aw_blog_post_form';

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var FieldsetFactory
     */
    private $rendererFieldsetFactory;

    /**
     * @var BlockConditions
     */
    private $conditions;

    /**
     * @var ProductFactory
     */
    private $productRuleFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var string
     */
    protected $nameInLayout = 'conditions_apply_to';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param PostRepositoryInterface $postRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param BlockConditions $conditions
     * @param FieldsetFactory $rendererFieldsetFactory
     * @param ProductFactory $productRuleFactory
     * @param DataPersistorInterface $dataPersistor
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        PostRepositoryInterface $postRepository,
        DataObjectProcessor $dataObjectProcessor,
        BlockConditions $conditions,
        FieldsetFactory $rendererFieldsetFactory,
        ProductFactory $productRuleFactory,
        DataPersistorInterface $dataPersistor,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->postRepository = $postRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->conditions = $conditions;
        $this->rendererFieldsetFactory = $rendererFieldsetFactory;
        $this->productRuleFactory = $productRuleFactory;
        $this->dataPersistor = $dataPersistor;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Get data for post
     *
     * @return array|null
     */
    protected function getFormData()
    {
        $formData = [];
        if (!empty($this->dataPersistor->get('aw_blog_post'))) {
            $formData = $this->dataObjectFactory->create(
                $this->dataPersistor->get('aw_blog_post')
            );
        } elseif ($id = $this->getRequest()->getParam('id')) {
            $formData = $this->postRepository->get($id);
        }
        if ($formData) {
            $formData = $this->dataObjectProcessor->buildOutputDataArray(
                $formData,
                PostInterface::class
            );
        }
        return $formData;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $formData = $this->getFormData();
        $productRule = $this->productRuleFactory->create();
        if (isset($formData['product_condition'])) {
            $productRule->setConditions([])
                ->getConditions()
                ->loadArray($formData['product_condition']);
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            [
                'comment' => __(
                    'Please specify products where the post should be displayed. '
                    . 'Leave blank to not display post.'
                )
            ]
        )->setRenderer(
            $this->rendererFieldsetFactory->create()
                ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
                ->setNewChildUrl(
                    $this->getUrl(
                        '*/*/newConditionHtml',
                        [
                            'form'   => $form->getHtmlIdPrefix() . 'conditions_fieldset',
                            'prefix' => 'conditions',
                            'rule'   => base64_encode(Product::class),
                            'form_namespace' => self::FORM_NAME
                        ]
                    )
                )
        );
        $productRule->setJsFormObject($form->getHtmlIdPrefix() . 'conditions_fieldset');
        $fieldset
            ->addField(
                'conditions',
                'text',
                [
                    'name'           => 'conditions',
                    'label'          => __('Conditions'),
                    'title'          => __('Conditions'),
                    'data-form-part' => self::FORM_NAME
                ]
            )
            ->setRule($productRule)
            ->setRenderer($this->conditions);

        $this->setConditionFormName(
            $productRule->getConditions(),
            self::FORM_NAME,
            $form->getHtmlIdPrefix() . 'conditions_fieldset'
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Handles addition of form name to condition and its conditions
     *
     * @param RuleAbstractCondition $conditions
     * @param string $formName
     * @param string $jsFormObject
     * @return void
     */
    private function setConditionFormName(RuleAbstractCondition $conditions, $formName, $jsFormObject)
    {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormObject);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormObject);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
