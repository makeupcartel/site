<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Ui\Component\Listing\Column;

use BitExpert\ForceCustomerLogin\Helper\Strategy\StrategyManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class StrategyName
 *
 * @package BitExpert\ForceCustomerLogin\Ui\Component\Listing\Column
 */
class StrategyName extends Column
{
    /**
     * @var StrategyManager
     */
    private $strategyManager;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param StrategyManager $strategyManager
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StrategyManager $strategyManager,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->strategyManager = $strategyManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['strategy']) || !$this->strategyManager->has($item['strategy'])) {
                continue;
            }

            $fieldName = $this->getData('name');

            $strategy = $this->strategyManager->get($item['strategy']);
            $item[$fieldName] = $strategy->getName();
        }

        return $dataSource;
    }
}
