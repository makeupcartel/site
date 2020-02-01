<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\Column\Product;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Price extends \Amasty\Ogrid\Model\Column\Product
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceFormatter;

    /**
     * BasePrice constructor.
     * @param $fieldKey
     * @param $resourceModel
     * @param \Amasty\Base\Model\Serializer $serializer
     * @param PriceCurrencyInterface $priceFormatter
     * @param \Magento\Framework\DB\Helper $dbHelper
     * @param array $columns
     * @param string $primaryKey
     * @param string $foreignKey
     */
    public function __construct(
        $fieldKey,
        $resourceModel,
        \Amasty\Base\Model\Serializer $serializer,
        PriceCurrencyInterface $priceFormatter,
        \Magento\Framework\DB\Helper $dbHelper,
        $columns = [],
        $primaryKey = 'entity_id',
        $foreignKey = 'entity_id'
    ) {
        $this->_priceFormatter = $priceFormatter;

        parent::__construct(
            $fieldKey,
            $resourceModel,
            $serializer,
            $dbHelper,
            $columns,
            $primaryKey,
            $foreignKey
        );
    }

    public function modifyItem(&$item, $config = [])
    {
        parent::modifyItem($item, $config);

        $currencyCode = isset($config['order_currency_code']) ? $config['order_currency_code'] : null;

        $item[$this->_alias_prefix . $this->_fieldKey] = $this->_priceFormatter->format(
            $item[$this->_alias_prefix . $this->_fieldKey],
            false,
            null,
            null,
            $currencyCode
        );
    }
}
