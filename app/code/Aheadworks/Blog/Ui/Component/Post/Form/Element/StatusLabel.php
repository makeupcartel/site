<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\Component\Post\Form\Element;

use Magento\Ui\Component\Form\Element\Input;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class StatusLabel
 * @package Aheadworks\Blog\Ui\Component\Post\Form\Element
 */
class StatusLabel extends Input
{
    /**
     * @var \Aheadworks\Blog\Model\Source\Post\Status
     */
    private $statusSource;

    /**
     * @param ContextInterface $context
     * @param \Aheadworks\Blog\Model\Source\Post\Status $statusSource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Aheadworks\Blog\Model\Source\Post\Status $statusSource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->statusSource = $statusSource;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (!isset($config['statusOptions'])) {
            $config['statusOptions'] = $this->statusSource->getOptions();
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}
