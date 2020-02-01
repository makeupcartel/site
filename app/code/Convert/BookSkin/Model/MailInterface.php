<?php

namespace Convert\BookSkin\Model;

/**
 * Interface MailInterface
 *
 * @package Convert\BookSkin\Model
 */
interface MailInterface
{
    /**
     * Send email from book skin form
     *
     * @param string $replyTo
     * @param array $variables
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function send($replyTo, array $variables);
}
