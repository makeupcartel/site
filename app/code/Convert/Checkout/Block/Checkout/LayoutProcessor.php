<?php

namespace Convert\Checkout\Block\Checkout;

/**
 * Class LayoutProcessor
 *
 * @package Convert\Checkout\Block\Checkout
 */
class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        // do not proceed if billing address is managed with ui-select
        if (empty($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list'])
        ) {
            return $jsLayout;
        }

        $jsLayout = $this->processValidateTelephoneForBillingAddress($jsLayout);
        return $jsLayout;
    }

    /**
     * Render shipping address for payment methods.
     *
     * @param array $jsLayout
     * @return array
     */
    private function processValidateTelephoneForBillingAddress(
        array $jsLayout
    ): array {
        // The following code is a workaround for custom address attributes
        $paymentMethodRenders = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children'];
        if (\is_array($paymentMethodRenders)) {
            foreach ($paymentMethodRenders as $name => $renderer) {
                if (isset($renderer['children']) && array_key_exists('form-fields', $renderer['children'])) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['payments-list']['children'][$name]['children']
                    ['form-fields']['children']['telephone']['validation']['cvalidate-phone-number'] = true;
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['payments-list']['children'][$name]['children']
                    ['form-fields']['children']['street']['children']['0']['validation']['max_text_length'] = 40;
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['payments-list']['children'][$name]['children']
                    ['form-fields']['children']['address_tag']['visible'] = false;
                }
            }
        }

        return $jsLayout;
    }
}
