<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Data as HelperData;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Rule\Model\Action\AbstractAction;
use Magento\Rule\Model\Condition\AbstractCondition;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\Profile as ProfileResource;
use Unirgy\RapidFlow\Model\Rule;

class NewConditionHtml extends AbstractProfile
{
    /**
     * @var Rule
     */
    protected $_rapidFlowRule;

    public function __construct(
        Context $context,
        Profile $profile,
        HelperData $helper,
        ProfileResource $resource,
        Rule $rule
    ) {
        $this->_rapidFlowRule = $rule;

        parent::__construct($context, $profile, $helper, $resource);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $form = $this->getRequest()->getParam('form');

        $model = $this->_objectManager->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->_rapidFlowRule)
            ->setPrefix($form);
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition
            || $model instanceof AbstractAction
        ) {
            $model->setJsFormObject('rule_' . $form . '_fieldset');
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents($html);
        return $result;
    }
}
