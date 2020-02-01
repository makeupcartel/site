<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Helper\Strategy;

/**
 * Class StrategyManager
 *
 * @package BitExpert\ForceCustomerLogin\Helper\Strategy
 */
class StrategyManager
{
    /*
     * Fallback
     */
    const DEFAULT_STRATEGY = 'default';

    /**
     * @var StrategyInterface[]
     */
    private $strategies;
    /**
     * @var string[]
     */
    private $strategyNames;

    /**
     * LoginRequiredOnVisitorInitObserver constructor.
     *
     * @param StrategyInterface[] $strategies
     */
    public function __construct(array $strategies)
    {
        foreach ($strategies as $identifier => $strategyEntry) {
            $this->strategies[$identifier] = $strategyEntry;
            $this->strategyNames[$identifier] = $strategyEntry->getName();
        }
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->strategies[$identifier]);
    }

    /**
     * @param $identifier
     * @return StrategyInterface
     */
    public function get($identifier)
    {
        if (!isset($this->strategies[$identifier])) {
            if (isset($this->strategies[self::DEFAULT_STRATEGY])) {
                return $this->strategies[self::DEFAULT_STRATEGY];
            }
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not load rule strategy with identifier "%s"',
                    $identifier
                )
            );
        }

        return $this->strategies[$identifier];
    }

    /**
     * @return StrategyInterface[]
     */
    public function getStrategies()
    {
        return $this->strategies;
    }

    /**
     * @return string[]
     */
    public function getStrategyNames()
    {
        return $this->strategyNames;
    }
}
