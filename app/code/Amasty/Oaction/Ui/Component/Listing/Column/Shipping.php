<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Oaction\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Shipping extends Column
{
    /**
     * @var \Amasty\Oaction\Helper\Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Amasty\Oaction\Helper\Data $helper,
        UiComponentFactory $uiComponentFactory,
        \Amasty\Oaction\Model\Source\Carriers $carrier,
        array $components = [],
        array $data = []
    )
    {
        $this->carrier = $carrier;
        $this->_helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();

        $data = $this->getData('config');
        $data['carrier'] = $this->carrier->toOptionArray();
        $data['default_carrier'] = $this->_helper->getModuleConfig('ship/carrier');
        $data['default_title'] = $this->_helper->getModuleConfig('ship/title');
        $data['show_title'] = $this->_helper->getModuleConfig('ship/comment') == "0"? 0: 1;

        $this->setData('config', $data);
    }

}