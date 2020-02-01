<?php
namespace Convert\SwarmingSubscribePro\Block\Product;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Tax\Model\Config as TaxConfig;
use Swarming\SubscribePro\Api\Data\ProductInterface;
use SubscribePro\Exception\InvalidArgumentException;
use SubscribePro\Exception\HttpException;

class Subscription extends \Swarming\SubscribePro\Block\Product\Subscription
{
    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function initJsLayout()
    {
        try {
            try {
                $platformProduct = $this->getPlatformProduct()->toArray();
            } catch (\Exception $e) {
                $platformProduct = [];
            }
        } catch (InvalidArgumentException $e) {
            $this->logger->debug('Could not load product from Subscribe Pro platform.');
            $this->logger->info($e->getMessage());
            $platformProduct = [];
        } catch (HttpException $e) {
            $this->logger->debug('Could not load product from Subscribe Pro platform.');
            $this->logger->info($e->getMessage());
            $platformProduct = [];
        }

        $data = [
            'components' => [
                'subscription-container' => [
                    'config' => [
                        'oneTimePurchaseOption' => ProductInterface::SO_ONETIME_PURCHASE,
                        'subscriptionOption' => ProductInterface::SO_SUBSCRIPTION,
                        'subscriptionOnlyMode' => ProductInterface::SOM_SUBSCRIPTION_ONLY,
                        'subscriptionAndOneTimePurchaseMode' => ProductInterface::SOM_SUBSCRIPTION_AND_ONETIME_PURCHASE,
                        'product' => $platformProduct,
                        'priceConfig' => $this->priceConfigProvider->getConfig()
                    ]
                ]
            ]
        ];

        $jsLayout = array_merge_recursive($this->jsLayout, $data);
        if ($this->isPriceHidden()) {
            $jsLayout['components']['subscription-container']['component'] = 'Swarming_SubscribePro/js/view/product/subscription-msrp';
            $jsLayout['components']['subscription-container']['config']['msrpPrice'] = $this->getMsrpPrice();
        }

        $this->jsLayout = $jsLayout;
    }
}
