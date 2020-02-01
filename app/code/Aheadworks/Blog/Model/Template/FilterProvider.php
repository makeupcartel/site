<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Template;

use Magento\Framework\ObjectManagerInterface;
use Magento\Cms\Model\Template\Filter;

/**
 * Template filter provider
 */
class FilterProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $filterClassName;

    /**
     * @var \Magento\Framework\Filter\Template|null
     */
    private $filterInstance = null;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $filterClassName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $filterClassName = Filter::class
    ) {
        $this->objectManager = $objectManager;
        $this->filterClassName = $filterClassName;
    }

    /**
     * Retrieves filter instance
     *
     * @return \Magento\Framework\Filter\Template|mixed|null
     * @throws \Exception
     */
    public function getFilter()
    {
        if ($this->filterInstance === null) {
            $filterInstance = $this->objectManager->get($this->filterClassName);
            if (!$filterInstance instanceof \Magento\Framework\Filter\Template) {
                throw new \Exception(
                    'Template filter ' . $this->filterClassName . ' does not implement required interface.'
                );
            }
            $this->filterInstance = $filterInstance;
        }
        return $this->filterInstance;
    }
}
