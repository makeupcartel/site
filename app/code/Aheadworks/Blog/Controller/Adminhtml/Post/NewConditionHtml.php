<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Post;

use Magento\Rule\Model\Condition\AbstractCondition;
use Aheadworks\Blog\Model\Rule\Product as ProductRule;

/**
 * Class NewConditionHtml
 * @package Aheadworks\Blog\Controller\Adminhtml\Post
 */
class NewConditionHtml extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Blog::posts';

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $prefix = 'conditions';
        if ($this->getRequest()->getParam('prefix')) {
            $prefix = $this->getRequest()->getParam('prefix');
        }

        $rule = ProductRule::class;
        if ($this->getRequest()->getParam('rule')) {
            $rule = base64_decode($this->getRequest()->getParam('rule'));
        }
        $model = $this->_objectManager
            ->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->_objectManager->create($rule))
            ->setPrefix($prefix);

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
