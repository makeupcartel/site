<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Cron;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Source\Post\Status;
use Aheadworks\Blog\Model\Flag;

/**
 * Class SchedulePost
 *
 * @package Aheadworks\Blog\Cron
 */
class SchedulePost extends CronAbstract
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isLocked(Flag::AW_BLOG_SCHEDULE_POST_LAST_EXEC_TIME)) {
            return $this;
        }
        $this->updateSchedulePosts();
        $this->setFlagData(Flag::AW_BLOG_SCHEDULE_POST_LAST_EXEC_TIME);
        return $this;
    }

    /**
     * Update schedule posts
     *
     * @return $this
     */
    private function updateSchedulePosts()
    {
        $now = $this->dateTime->gmtDate(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $this->searchCriteriaBuilder
            ->addFilter(PostInterface::STATUS, Status::SCHEDULED)
            ->addFilter(PostInterface::PUBLISH_DATE, $now, 'lteq');

        $scheduledPosts = $this->postRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        foreach ($scheduledPosts as $scheduledPost) {
            $scheduledPost->setStatus(Status::PUBLICATION);
            $this->postRepository->save($scheduledPost);
        }
        return $this;
    }
}
