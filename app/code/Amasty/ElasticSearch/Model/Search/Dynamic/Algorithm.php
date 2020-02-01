<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search\Dynamic;

use Magento\Framework\Search\Dynamic\Algorithm as MagentoAlgorithm;
use Magento\Framework\Search\Dynamic\IntervalInterface;

/**
 * Class Algorithm
 * Class implemented for fixing magento bug, related to notice "Undefined index in Search\Dynamic\Algorithm"
 * @package Amasty\ElasticSearch\Model\Search\Dynamic
 */
class Algorithm extends MagentoAlgorithm
{
    /**
     * @param int $quantileNumber
     * @param IntervalInterface $interval
     * @return array|false
     */
    protected function _findValueSeparator($quantileNumber, IntervalInterface $interval)
    {
        if ($quantileNumber < 1 || $quantileNumber >= $this->getIntervalsNumber()) {
            return null;
        }

        $values = [];
        $quantileInterval = $this->_getQuantileInterval($quantileNumber);
        $intervalValuesCount = $quantileInterval[1] - $quantileInterval[0] + 1;
        $offset = $quantileInterval[0];
        if ($this->_lastValueLimiter[0] !== null) {
            $offset -= $this->_lastValueLimiter[0];
        }
        if ($offset < 0) {
            $intervalValuesCount += $offset;
            $values = array_slice(
                $this->_values,
                $this->_lastValueLimiter[0] + $offset - $this->_quantileInterval[0],
                -$offset
            );
            $offset = 0;
        }
        $lowerValue = $this->_lastValueLimiter[1];
        if ($this->_lowerLimit !== null) {
            $lowerValue = max($lowerValue, $this->_lowerLimit);
        }
        if ($intervalValuesCount >= 0) {
            $values = array_merge(
                $values,
                $interval->load($intervalValuesCount + 1, $offset, $lowerValue, $this->_upperLimit)
            );
        }

        $bestRoundValue = [];
        if (isset($values[0]) && isset($values[$intervalValuesCount - 1])) {
            $lastValue = $values[$intervalValuesCount - 1];
            if ($lastValue == $values[0]) {
                if ($quantileNumber == 1 && $offset) {
                    $additionalValues = $interval->loadPrevious($lastValue, $quantileInterval[0], $this->_lowerLimit);
                    if ($additionalValues) {
                        $quantileInterval[0] -= count($additionalValues);
                        $values = array_merge($additionalValues, $values);
                        $bestRoundValue = $this->_findRoundValue(
                            $values[0] + self::MIN_POSSIBLE_VALUE / 10,
                            $lastValue,
                            false
                        );
                    }
                }
                if ($quantileNumber == $this->getIntervalsNumber() - 1) {
                    $valuesCount = count($values);
                    if ($values[$valuesCount - 1] > $lastValue) {
                        $additionalValues = [$values[$valuesCount - 1]];
                    } else {
                        $additionalValues = $interval->loadNext(
                            $lastValue,
                            $this->_count - $quantileInterval[0] - count($values),
                            $this->_upperLimit
                        );
                    }
                    if ($additionalValues) {
                        $quantileInterval[1] = $quantileInterval[0] + count($values) - 1;
                        if ($values[$valuesCount - 1] <= $lastValue) {
                            $quantileInterval[1] += count($additionalValues);
                            $values = array_merge($values, $additionalValues);
                        }
                        $upperBestRoundValue = $this->_findRoundValue(
                            $lastValue + self::MIN_POSSIBLE_VALUE / 10,
                            $values[count($values) - 1],
                            false
                        );
                        $this->_mergeRoundValues($bestRoundValue, $upperBestRoundValue);
                    }
                }
            } else {
                $bestRoundValue = $this->_findRoundValue(
                    $values[0] + self::MIN_POSSIBLE_VALUE / 10,
                    $lastValue
                );
            }
        }
        $this->_quantileInterval = $quantileInterval;
        $this->_values = $values;

        if (empty($bestRoundValue)) {
            $this->_skippedQuantilesUpperLimits[$quantileNumber] = $quantileInterval[1];

            return $bestRoundValue;
        }

        $valuesCount = count($values);
        if ($values[$valuesCount - 1] > $lastValue) {
            $this->_lastValueLimiter = [$quantileInterval[0] + $valuesCount - 1, $values[$valuesCount - 1]];
        }

        ksort($bestRoundValue, SORT_NUMERIC);
        foreach ($bestRoundValue as $index => &$bestRoundValueValues) {
            if (empty($bestRoundValueValues)) {
                unset($bestRoundValue[$index]);
            } else {
                sort($bestRoundValueValues);
            }
        }

        return array_reverse($bestRoundValue);
    }
}
