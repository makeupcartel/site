<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Observer\Category;

use Magento\Framework\Event\ObserverInterface;

/**
 * observer for event controller_action_predispatch_catalog_category_view
 */
class ViewPredispatch implements ObserverInterface
{
    /**
     * @var \Amasty\Groupcat\Model\ProductRuleProvider
     */
    private $ruleProvider;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * ViewPredispatch constructor.
     *
     * @param \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider
     * @param \Amasty\Groupcat\Helper\Data               $helper
     */
    public function __construct(
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider,
        \Amasty\Groupcat\Helper\Data $helper
    ) {
        $this->ruleProvider = $ruleProvider;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isModuleEnabled()) {
            return;
        }

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        $categoryId = $request->getParam('id');
        if (!$categoryId) {
            return;
        }

        $rule = $this->ruleProvider->getRulesForCategoryView($categoryId)
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();

        if ($rule->getId()) {
            $this->helper->setRedirect($observer->getEvent()->getControllerAction(), $rule);
        }
    }
}
