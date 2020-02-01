<?php

namespace Convert\SwarmingSubscribePro\Block\Checkout\Onepage\Success;

use Swarming\SubscribePro\Model\Quote\SubscriptionCreator;

class Subscriptions extends \Swarming\SubscribePro\Block\Checkout\Onepage\Success\Subscriptions
{
    /**
     * @return int
     */
    public function getCountSubscriptions()
    {
        if(!$this->checkoutSession->getData(SubscriptionCreator::CREATED_SUBSCRIPTION_IDS)){
            return 0;
        }
        return parent::getCountSubscriptions();
    }
}
