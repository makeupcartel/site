<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Block\Adminhtml\Form;

use Magento\Framework\View\Element\Template;

class HighlightPromotion extends Template
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $manager;

    protected $_template = 'highlight_promotion.phtml';

    public function __construct(Template\Context $context, \Magento\Framework\Module\Manager $manager, array $data = [])
    {
        parent::__construct($context, $data);
        $this->manager = $manager;
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isModuleEnable($moduleName)
    {
        return $this->manager->isEnabled($moduleName);
    }
}
