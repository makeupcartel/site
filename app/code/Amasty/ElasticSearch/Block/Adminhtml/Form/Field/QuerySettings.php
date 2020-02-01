<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Amasty\ElasticSearch\Model\Config\QuerySettings as BackendModel;

class QuerySettings extends AbstractFieldArray
{
    const STYLE = 'style="width:90px"';

    /**
     * @var null|Attribute
     */
    private $attributeRenderer;

    /**
     * @var null|YesNo
     */
    private $wildcardRenderer;

    /**
     * @var null|YesNo
     */
    private $spellCorrectionRenderer;

    /**
     * @var null|Combining
     */
    private $combiningRenderer;

    /**
     * @var \Amasty\ElasticSearch\Model\Source\FulltextAttributes
     */
    private $fulltextAttributesSource;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->fulltextAttributesSource = $this->getData('fulltext_attributes');
    }

    /**
     * @return Attribute|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeRenderer()
    {
        if (!$this->attributeRenderer) {
            $this->attributeRenderer = $this->getLayout()->createBlock(
                Attribute::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->attributeRenderer
                ->setFulltextAttributes($this->fulltextAttributesSource->toArray())
                ->setExtraParams(self::STYLE);
        }

        return $this->attributeRenderer;
    }

    /**
     * @return YesNo|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getWildcardRenderer()
    {
        if (!$this->wildcardRenderer) {
            $this->wildcardRenderer = $this->getLayout()->createBlock(
                YesNo::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->wildcardRenderer->setExtraParams(self::STYLE);
        }

        return $this->wildcardRenderer;
    }

    /**
     * @return YesNo|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSpellCorrectionRenderer()
    {
        if (!$this->spellCorrectionRenderer) {
            $this->spellCorrectionRenderer = $this->getLayout()->createBlock(
                YesNo::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->spellCorrectionRenderer->setExtraParams(self::STYLE);
        }

        return $this->spellCorrectionRenderer;
    }
    /**
     * @return YesNo|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCombiningRenderer()
    {
        if (!$this->combiningRenderer) {
            $this->combiningRenderer = $this->getLayout()->createBlock(
                Combining::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->combiningRenderer->setExtraParams(self::STYLE);
        }

        return $this->combiningRenderer;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->setTemplate('system/config/array.phtml');
        $this->addColumn(
            BackendModel::ATTRIBUTE,
            ['label' => __('Attribute'), 'renderer' => $this->getAttributeRenderer()]
        );
        $this->addColumn(
            BackendModel::WILDCARD,
            ['label' => __('Wildcard'), 'renderer' => $this->getWildcardRenderer()]
        );
        $this->addColumn(
            BackendModel::SPELLING,
            ['label' => __('Spell Correction'), 'renderer' => $this->getSpellCorrectionRenderer()]
        );
        $this->addColumn(
            BackendModel::COMBINING,
            ['label' => __('Match Mode'), 'renderer' => $this->getCombiningRenderer()]
        );
    }

    /**
     * @inheritdoc
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionId = $this->getAttributeRenderer()->calcOptionHash($row->getData(BackendModel::ATTRIBUTE));
        $value = $this->fulltextAttributesSource->toArray()[$row->getData(BackendModel::ATTRIBUTE)];
        $optionExtraAttr['option_'. $optionId] = $value;

        $optionId = $this->getWildcardRenderer()->calcOptionHash($row->getData(BackendModel::WILDCARD));
        $optionExtraAttr['option_'. $optionId] = ' checked ';

        $optionId = $this->getSpellCorrectionRenderer()->calcOptionHash($row->getData(BackendModel::SPELLING));
        $optionExtraAttr['option_'. $optionId] = ' checked ';

        $optionId = $this->getCombiningRenderer()->calcOptionHash($row->getData(BackendModel::COMBINING));
        $optionExtraAttr['option_'. $optionId] = 'selected="selected"';

        $row->setData('option_extra_attrs', $optionExtraAttr);
    }

    /**
     * @return string
     */
    public function getTooltipText()
    {
        return '<b>Wildcard</b> allows shoppers to input a half-finished word and get a relevant result<br/>
                        <b>Spell Correction</b> enables automatic spelling correction.<br/>
                        <b>Match Mode</b> is about how the words are combined in a search query. <br/>
                        Please note, Wildcard and Spell Correction don\'t work at the same time.<br/>';
    }
}
