<?php

namespace Convert\OldOrder\Block\Adminhtml\OldOrder;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Convert\OldOrder\Model\OldOrderFactory;

class Form extends Generic
{
	public function __construct(
		Context $context,
		Registry $registry,
		FormFactory $formFactory,
		OldOrderFactory $postFactory,
		array $data)
	{
		$this->_postFactory = $postFactory;
		parent::__construct($context, $registry, $formFactory, $data);
	}
	public function _prepareForm()
	{
		$model = $this->_postFactory->create();
		$id = $this->getRequest()->getParam('id');
		$infoPost = $model->load($id)->getData(); 
		$a = true;
		if (@$infoPost['status'] == 1) {
			$a = true;
		} else {
			$a = false;
		}

		$form = $this->_formFactory->create(
			[
				"data" => [
					"id" => "edit_form",
					"action" => $this->getUrl("blog/post/save"),
					"method" => "post",
					"enctype" => "multipart/form-data"
				]
			]
		);
		$fieldset = $form->addFieldset(
			"base_fieldset",
			["legend" => __("Edit")]
		);
		if(!empty($infoPost['id'])){
			$fieldset->addField(
				"id",
				"hidden",
				[
					"name" => "post_id",
					"label" => __("id Post"),
					"value" => $infoPost['id'],
					"required" => true,
				]
			);

		}
	

		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}