<?php

namespace Convert\AfterpayBundles\Model\Adapter\V1;

use Afterpay\Afterpay\Model\Adapter\Afterpay\Call;
use Afterpay\Afterpay\Model\Config\Payovertime as PayovertimeConfig;
use Magento\Framework\ObjectManagerInterface as ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Afterpay\Afterpay\Helper\Data as Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\CountryFactory as CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;

/**
 * Class AfterpayOrderTokenV1
 *
 * @package Convert\AfterpayBundles\Model\Adapter\V1
 */
class AfterpayOrderTokenV1 extends \Afterpay\Afterpay\Model\Adapter\V1\AfterpayOrderTokenV1
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * AfterpayOrderToken constructor.
     * @param Call $afterpayApiCall
     * @param PayovertimeConfig $afterpayConfig
     * @param ObjectManagerInterface $objectManagerInterface
     * @param StoreManagerInterface $storeManagerInterface
     * @param JsonHelper $jsonHelper
     * @param CountryFactory $countryFactory
     * @param ScopeConfig $scopeConfig
     * @param Helper $afterpayHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Call $afterpayApiCall,
        PayovertimeConfig $afterpayConfig,
        ObjectManagerInterface $objectManagerInterface,
        StoreManagerInterface $storeManagerInterface,
        JsonHelper $jsonHelper,
        CountryFactory $countryFactory,
        ScopeConfig $scopeConfig,
        Helper $afterpayHelper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_productRepository = $productRepository;
        parent::__construct(
            $afterpayApiCall,
            $afterpayConfig,
            $objectManagerInterface,
            $storeManagerInterface,
            $jsonHelper,
            $countryFactory,
            $scopeConfig,
            $afterpayHelper
        );
    }
    /**
     * Build XML for order token
     *
     * @param \Magento\Sales\Model\Order $object Order to get token for
     * @param array $override
     * @return array
     */
    protected function _buildOrderTokenRequest($object, $code, $override = [])
    {
        $precision = self::DECIMAL_PRECISION;

        $params = [
            'paymentType' => $code
        ];
        $data = $object->getData();
        $billingAddress  = $object->getBillingAddress();
        $shippingAddress = $object->getShippingAddress();


        //check if shipping address is missing - e.g. Gift Cards
        if (empty($shippingAddress) || empty($shippingAddress->getStreetLine(1))) {
            $shippingAddress = $object->getBillingAddress();
        } elseif (empty($billingAddress) || empty($billingAddress->getStreetLine(1))) {
            $billingAddress = $object->getShippingAddress();
        }


        $email = $object->getCustomerEmail();

        $params['consumer'] = [
            'email'      => (string)$email,
            'givenNames' => $object->getCustomerFirstname() ? (string)$object->getCustomerFirstname() : $billingAddress->getFirstname(),
            'surname'    => $object->getCustomerLastname() ? (string)$object->getCustomerLastname() : $billingAddress->getLastname(),
            'mobile'     => (string)$billingAddress->getTelephone()
        ];
        
        $params['merchantReference'] = array_key_exists('merchantOrderId', $override) ? $override['merchantOrderId'] : $object->getIncrementId();

        $params['merchant'] = [
            'redirectConfirmUrl'    => $this->_storeManagerInterface->getStore($object->getStore()->getId())->getBaseUrl() . 'afterpay/payment/response',
            'redirectCancelUrl'     => $this->_storeManagerInterface->getStore($object->getStore()->getId())->getBaseUrl() . 'afterpay/payment/response'
        ];

        foreach ($object->getAllVisibleItems() as $item) {
            if (!$item->getParentItem()) {
                if('bundle' === $item->getProductType() && strlen((string)$item->getSku()) > 128){
                    $product = $this->_productRepository->getById($item->getProductId());
                    $itemSku = $product->getSku();
                }else{
                    $itemSku = $item->getSku();
                }
                $params['items'][] = [
                    'name'     => (string)$item->getName(),
                    'sku'      => (string)$itemSku,
                    'quantity' => (int)$item->getQty(),
                    'price'    => [
                        'amount'   => round((float)$item->getPriceInclTax(), $precision),
                        'currency' => (string)$data['store_currency_code']
                    ]
                ];
            }
        }
        if ($object->getShippingInclTax()) {
            $params['shippingAmount'] = [
                'amount'   => round((float)$object->getShippingInclTax(), $precision), // with tax
                'currency' => (string)$data['store_currency_code']
            ];
        }
        if (isset($data['discount_amount'])) {
            $params['discounts']['displayName'] = 'Discount';
            $params['orderDetail']['amount']     = [
                'amount'   => round((float)$data['discount_amount'], $precision),
                'currency' => (string)$data['store_currency_code']
            ];
        }
        $taxAmount = array_key_exists('tax_amount', $data) ? $data['tax_amount'] : $shippingAddress->getTaxAmount();
        $params['taxAmount'] = [
            'amount'   => isset($taxAmount) ? round((float)$taxAmount, $precision) : 0,
            'currency' => (string)$data['store_currency_code']
        ];
        $street = $shippingAddress->getStreet();
        $params['shipping'] = [
            'name'          => (string)$shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
            'line1'         => (string)$shippingAddress->getStreetLine(1),
            'line2'         => (string)$shippingAddress->getStreetLine(2),
            'suburb'        => (string)$shippingAddress->getCity(),
            'postcode'      => (string)$shippingAddress->getPostcode(),
            'state'         => (string)$shippingAddress->getRegion(),
            'countryCode'   => (string)$shippingAddress->getCountryId(),
            // 'countryCode'   => 'AU',
            'phoneNumber'   => (string)$shippingAddress->getTelephone(),
        ];
        $street = $billingAddress->getStreet();
        $params['billing'] = [
            'name'          => (string)$billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
            'line1'         => (string)$billingAddress->getStreetLine(1),
            'line2'         => (string)$billingAddress->getStreetLine(2),
            'suburb'        => (string)$billingAddress->getCity(),
            'postcode'      => (string)$billingAddress->getPostcode(),
            'state'         => (string)$billingAddress->getRegion(),
            'countryCode'   => (string)$billingAddress->getCountryId(),
            // 'countryCode'   => 'AU',
            'phoneNumber'   => (string)$billingAddress->getTelephone(),
        ];
        $params['totalAmount'] = [
            'amount'   => round((float)$object->getGrandTotal(), $precision),
            'currency' => (string)$data['store_currency_code'],
        ];

        return $params;
    }
}
