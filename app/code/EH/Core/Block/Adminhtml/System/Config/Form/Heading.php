<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2018 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use EH\Core\Helper\Data as CoreHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Component\ComponentRegistrar;

class Heading extends Field
{
    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @param CoreHelper $coreHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        CoreHelper $coreHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreHelper = $coreHelper;
    }

    /**
     * Return heading block html
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $extensionName = $element->getLegend()->getText();
        $extensionVersion = $this->coreHelper->getComposerVersion($extensionName, ComponentRegistrar::MODULE);
        $extensionDetails = $this->coreHelper->getExtensionVersion($extensionName);
        $html = '<div class="eh-heading" style="padding:12px;margin:0 0 10px 0;background-color:#f8f8f8;border: 1px solid #dddddd;">
            <div class="row-1" style="display: block">
            <span class="logo"><img src="'.CoreHelper::LOGO.'"></span>
            <a style="float: right" type="button" class="action- scalable action-secondary" data-ui-id="view-extensions-button" target="_blank" href="'.CoreHelper::VEL.'">
                <span>'.__("View More Extensions").'</span>
            </a>
            </div>';
        if(isset($extensionDetails['label'])) {
            $html.='<span class="content row-2" style="display: block;margin-top: 5px;">' .$extensionDetails['label'] . '<span style="color: #ef6262; font-weight: bold"> v' . $extensionVersion . '</span> is developed by <a href="'.CoreHelper::EL.'" target="_blank">'.CoreHelper::EN.'</a>. <a href="'.CoreHelper::SL.'" target="_blank">'.__("Need help?").'</a></span>';
        }
        $html.='</div>';

        if(isset($extensionDetails['update_needed']) && $extensionDetails['update_needed']) {
            $html .= '<div class="eh-update-notification" style="padding:22px 12px 20px 34px;position: relative;margin:0 0 10px 0;background-color:#e9fbdb;">
            '.__("New version").' '.__($extensionDetails['status_message']).' '.__($extensionDetails['notification_msg']).'
        </div>';
        }

        return $html;
    }
}
