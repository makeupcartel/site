<?php
namespace Dfe\OrderNotifier;

use Magento\Catalog\Helper\Image as ImageH;
use Magento\Catalog\Model\Product as P;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Config;
use Magento\Framework\App\ObjectManager as OM;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\RequestInterface as IRequest;
use Magento\Framework\DataObject as _DO;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress as RA;
use Magento\Framework\View\Config as ViewConfig;
use Magento\Framework\View\ConfigInterface as ViewConfigI;
use Magento\Sales\Api\Data\OrderAddressInterface as IOA;
use Magento\Sales\Api\Data\OrderItemInterface as IOI;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Address as OA;
use Magento\Sales\Model\Order\Item as OI;
use Magento\Store\Model\ScopeInterface as SS;

class Payload {

	/**
	 * 2018-08-11
	 * @used-by get()
	 * @var O
	 */
	protected $_o;

	/**
	 * 2018-08-11
	 * @used-by get()
	 * @param IOA|OA|null $a
	 * @return array(string => mixed)
	 */
	public function address(IOA $a) {
		return !$a ? [] : self::remove_objects($a);
	}

	/**
	 * 2018-08-11
	 * @used-by get()
	 * @return array(string => mixed)
	 */
	public function items()
	{
		return self::oqi_leafs($this->_o, function(OI $i) {
		    /* check if the product is available */
		    $product = $i->getProduct();
		    if ($product && $product instanceof P) {
                $imageUrl = self::product_image_url($i->getProduct());
            } else {
		        $imageUrl = '';
            }
			return [
				'id' => $i->getProductId(),
				'image' => $imageUrl,
				'name' => $i->getName(),
				'price' => self::oqi_price($i),
				'price_with_discount' => self::oqi_price($i, false, true),
				'price_with_discount_and_tax' => self::oqi_price($i, true, true),
				'price_with_tax' => self::oqi_price($i, true),
				'qty' => intval($i->getQtyOrdered()),
				'sku' => $i->getSku(),
				'tax_rate' => self::oqi_tax_rate($i),
				'url' => self::oqi_url($i)
			];
		});
	}

	/**
	 * 2018-08-11
	 * @used-by get()
	 * @return array(string => mixed)
	 */
	public function payment() {
		return array_filter(self::remove_objects($this->_o->getPayment()),function($k) {
			return 0 !== strpos($k, 'cc_');
		},ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * 2018-08-11
	 * @used-by get()
	 * @return array(string => mixed)
	 */
	public function visitor() {
		$om = OM::getInstance(); /** @var OM $om */
		$ra = $om->get(RA::class); /** @var RA $a */
		$req = $om->get(IRequest::class); /** @var IRequest|RequestHttp $req */
		return self::clean([
			'http_user_agent' => $req->getHeader('user-agent')
			,'http_x_forwarded_for' => @$_ENV['HTTP_X_FORWARDED_FOR']
			,'http_via' => @$_ENV['HTTP_VIA']
			,'remote_addr' => $ra->getRemoteAddress()
		]);
	}

	/**
	 * 2018-08-11
	 * @used-by \Dfe\OrderNotifier\Observer\OrderSaveAfter::execute()
	 * @param O $o
	 * @return array(string => mixed)
	 */
	public function get(O $o)
	{
		$om = OM::getInstance(); /** @var OM $om */
        $customerData = [];
		$cr = $om->get(CustomerRegistry::class); /** @var CustomerRegistry $cr */
		if ($cid = $o->getCustomerId()) {
            try {
                $customer = $cr->retrieve($cid);
                $customerData = self::remove_objects($customer);
                if ($customer->getData('netsuite_id')) {
                    $customerData['entity_id'] = $customer->getData('netsuite_id');
                }
            } catch (\Exception $e) {

            }
        }
		$cfg = $om->get(Config::class); /** @var Config $cfg */
		$address = $o->getBillingAddress();
		if (!$o->getIsVirtual() && $o->getShippingAddress()) {
			$address = $o->getShippingAddress();
		}
		$i = new self;
		$i->_o = $o;
        $debug = $cfg->getValue('mage2pro/order_notifier/debug', SS::SCOPE_STORE, $o->getStore());
        if ($debug) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/order-notify.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Customer Data', $customerData);
            $logger->info('Item Data', $i->items());
        }
		return [
			'billing_address' => $i->address($o->getBillingAddress()),
            'customer' => $customerData,
            'line_items' => $i->items(),
            'payment' => $i->payment(),
            'shipping_address' => $i->address($address),
            'visitor' => $i->visitor()
		] + self::remove_objects($o);
	}

	/**
	 * 2015-02-07
	 * @param mixed[] $a
	 * @return mixed[]
	 */
	public function clean($a) {
		return array_filter($a, function($v) {
			return !in_array($v, ['', null, [], false], true);
		});
	}

	/**
	 * 2016-09-07
	 * @param O $oq
	 * @param \Closure $f
	 * @return array(int => mixed)
	 */
	public function oqi_leafs($oq, \Closure $f) {
		return array_map($f, array_values(array_filter($oq->getItems(), function($i) {/** @var OI $i */
			return !$i->getChildrenItems();
		})));
	}

	/**
	 * 2016-05-03
	 * 2017-09-25 The function returns the product unit price, not the order row price.
	 * 2017-09-30
	 * I have added the $afterDiscount flag.
	 * It is used oly for the shopping cart price rules.
	 * $afterDiscount = false: the functon will return a result BEFORE discounts subtraction.
	 * $afterDiscount = true: the functon will return a result AFTER discounts subtraction.
	 * For now, I use $afterDiscount = true only for Yandex.Market:
	 * Yandex.Kassa does not provide a possibility to specify the shopping cart discounts in a separayte row,
	 * so I use $afterDiscount = true.

	 * @used-by df_oqi_tax_rate()
	 * @param OI|IOI $i
	 * @param bool $withTax [optional]
	 * @param bool $withDiscount [optional]
	 * @return float
	 */
	public function oqi_price($i, $withTax = false, $withDiscount = false)
	{
		$r = floatval($withTax ? $i->getBasePriceInclTax() : $i->getBasePrice()) ?: (
		$i->getParentItem() ? self::oqi_price($i->getParentItem(), $withTax) : .0
		); /** @var float $r */
		/**
		 * 2017-09-30
		 * We should use @uses df_oqi_top(), because the `discount_amount` and `base_discount_amount` fields
		 * are not filled for the configurable children.
		 */
		return !$withDiscount ? $r : ($r - self::oqi_top($i)->getBaseDiscountAmount() / intval($i->getQtyOrdered()));
	}

	/**
	 * 2017-03-06
	 * Возвращает налоговую ставку для позиции заказа в процентах.
	 * $asInteger == false: 17.5% => 17.5.
	 * $asInteger == true: 17.5% => 1750
	 * 2017-09-30
	 * @todo Why do not just use \Magento\Sales\Model\Order\Item::getTaxPercent()?
	 * @param OI $i
	 * @param bool $asInteger [optional]
	 * @return float|int
	 */
	public function oqi_tax_rate($i, $asInteger = false)
	{
		$r = self::tax_rate(self::oqi_price($i, true), self::oqi_price($i));  /** @var float $r */
		return !$asInteger ? $r : round(100 * $r);
	}

	/**
	 * 2016-08-18
	 * @param OI $i
	 * @return OI
	 */
	public function oqi_top($i) {
		return $i->getParentItem() ?: $i;
	}

	/**
	 * 2017-02-01
	 * @param OI $i
	 * @return string
	 */
	public function oqi_url($i) {
		return self::oqi_top($i)->getProduct()->getProductUrl();
	}

	/**
	 * 2016-04-23
	 * How to get an image URL for a product programmatically?
	 * https://mage2.pro/t/1313
	 * How is @uses \Magento\Catalog\Helper\Image::getUrl() implemented and used?
	 * https://mage2.pro/t/1316
	 * @param P $p
	 * @param string|null $type [optional]
	 * @param array(string => string) $attrs [optional]
	 * @return string
	 */
	public function product_image_url(P $p, $type = null, $attrs = [])
	{
		$om = OM::getInstance(); /** @var OM $om */
		$h = $om->get(ImageH::class); /** @var ImageH $h */
		$vc = $om->get(ViewConfigI::class); /** @var ViewConfig|ViewConfigI $vc */
		/** @var string|null $r */
		if ($type) {
			$r = $h->init($p, $type, ['type' => $type] + $attrs + $vc->getViewConfig()->getMediaAttributes(
					'Magento_Catalog', ImageH::MEDIA_TYPE_CONFIG_NODE, $type)
			)->getUrl();
		} else {
			/**
			 * 2016-05-02
			 * How is @uses \Magento\Catalog\Model\Product::getMediaAttributes() implemented and used?
			 * https://mage2.pro/t/1505
			 * @var string[] $types
			 */
			$types = array_keys($p->getMediaAttributes());
			// Give priority to the «image» attribute.
			$key = array_search('image', $types); /** @var int|null $key */
			if (false !== $key) {
				unset($types[$key]);
				array_unshift($types, 'image');
			}
			$r = '';
			foreach ($types as $type) {
				$r = self::product_image_url($p, $type, $attrs);
				if ($r) {
					break;
				}
			}
		}
		// 2018-09-05
		// «The product image URLs are malformed on Windows»:
		// https://github.com/mage2pro/order-notifier/issues/3
		return str_replace('\\', '/', str_replace('\//', '/', $r));
	}

	/**
	 * 2018-08-11
	 * @used-by self::remove_objects()
	 * @used-by \Dfe\OrderNotifier\Payload::address()
	 * @used-by \Dfe\OrderNotifier\Payload::get()
	 * @used-by \Dfe\OrderNotifier\Payload::payment()
	 * @param _DO|mixed[] $v
	 * @return mixed
	 */
	public function remove_objects($v)
	{
		return self::clean(array_filter(
				is_array($v) ? $v : $v->getData(), function($v) {
				return is_object($v) ? false : (!is_array($v) ? true : self::remove_objects($v));
			})
		);
	}

	/**
	 * 2017-09-30
	 * 2017-11-03
	 * «Division by zero in mage2pro/core/Tax/lib/main.php on line 11»
	 * https://github.com/mage2pro/core/issues/42
	 * It is normal for an order position to have zero price: e.g., in case of free shipping.
	 * @param float $withTax
	 * @param float $withoutTax
	 * @return float
	 */
	public function tax_rate($withTax, $withoutTax)
	{
		return !$withoutTax ? 0 : 100 * ($withTax - $withoutTax) / $withoutTax;
	}
}