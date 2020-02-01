<?php
/**
 * Created by pp
 * @project magento2
 */

namespace Unirgy\RapidFlowPro\Block\Adminhtml\Profile;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Source;

class BaseForm extends Generic
{
    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var Source
     */
    protected $_source;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        HelperData $rapidFlowHelper,
        Source $source,
        array $data = []
    ) {

        $this->_helper = $rapidFlowHelper;
        $this->_source = $source;
        parent::__construct($context, $registry, $formFactory, $data);
    }
}
