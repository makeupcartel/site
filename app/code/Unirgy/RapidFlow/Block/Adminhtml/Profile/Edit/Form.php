<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Data\FormFactory;

class Form extends WidgetForm
{
    /**
     * @var FormFactory
     */
    protected $_formFactory;

    public function __construct(Context $context,
                                array $data,
                                FormFactory $formFactory
    )
    {
        $this->_formFactory = $formFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save',
                                              ['id' => $this->getRequest()->getParam('id')]),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
