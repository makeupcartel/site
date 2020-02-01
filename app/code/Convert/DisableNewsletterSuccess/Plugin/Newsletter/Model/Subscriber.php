<?php

namespace Convert\DisableNewsletterSuccess\Plugin\Newsletter\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Newsletter\Model\Subscriber as Subject;

/**
 * Class Subscriber
 *
 * @package Convert\DisableNewsletterSuccess\Plugin\Newsletter\Model
 */
class Subscriber
{
    const DISABLE_NEWSLETTER_SUCCESS = 'newsletter/subscription/disable_newsletter_success';
    
    /**
     * @var ScopeConfigInterface 
     */
    protected $scopeConfig;
    
    /**
     * Subscriber constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    protected function isNewsletterSuccessDisabled()
    {
        return $this->scopeConfig->getValue(self::DISABLE_NEWSLETTER_SUCCESS,ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * @param Subject $subject
     */
    public function beforeSendConfirmationSuccessEmail(Subject $subject)
    {
        if ($this->isNewsletterSuccessDisabled()) {
            $subject->setImportMode(true);
        }
    }

    /**
     * @param Subject $subject
     */
    public function beforeSendUnsubscriptionEmail(Subject $subject)
    {
        if ($this->isNewsletterSuccessDisabled()) {
            $subject->setImportMode(true);
        }
    }
}