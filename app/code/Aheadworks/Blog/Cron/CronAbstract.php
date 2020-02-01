<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Cron;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Blog\Model\ResourceModel\PostRepository;
use Aheadworks\Blog\Model\Flag;
use Aheadworks\Blog\Model\FlagFactory;

/**
 * Class CronAbstract
 *
 * @package Aheadworks\Blog\Cron
 */
abstract class CronAbstract
{
    /**
     * Cron run interval in seconds
     */
    const RUN_INTERVAL = 50;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var PostRepository
     */
    protected $postRepository;

    /**
     * @var Flag
     */
    private $flag;

    /**
     * @param DateTime $dateTime
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PostRepository $postRepository
     * @param FlagFactory $flagFactory
     */
    public function __construct(
        DateTime $dateTime,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PostRepository $postRepository,
        FlagFactory $flagFactory
    ) {
        $this->dateTime = $dateTime;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->postRepository = $postRepository;
        $this->flag = $flagFactory->create();
    }

    /**
     * Main cron job entry point
     *
     * @return $this
     */
    abstract public function execute();

    /**
     * Is cron job locked
     *
     * @param string $flag
     * @param int $interval
     * @return bool
     */
    protected function isLocked($flag, $interval = self::RUN_INTERVAL)
    {
        $now = $this->getCurrentTime();
        $lastExecTime = (int)$this->getFlagData($flag);
        return $now < $lastExecTime + $interval;
    }

    /**
     * Set flag data
     *
     * @param string $param
     * @return $this
     */
    protected function setFlagData($param)
    {
        $this->flag
            ->unsetData()
            ->setBlogFlagCode($param)
            ->loadSelf()
            ->setFlagData($this->getCurrentTime())
            ->save();

        return $this;
    }

    /**
     * Get current time
     *
     * @return int
     */
    private function getCurrentTime()
    {
        $now = $this->dateTime->timestamp();
        return $now;
    }

    /**
     * Get flag data
     *
     * @param string $param
     * @return mixed
     */
    private function getFlagData($param)
    {
        $this->flag
            ->unsetData()
            ->setBlogFlagCode($param)
            ->loadSelf();

        return $this->flag->getFlagData();
    }
}
