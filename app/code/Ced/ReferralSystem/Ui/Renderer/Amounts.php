<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_ReferralSystem
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\ReferralSystem\Ui\Renderer;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Amounts
 * @package Ced\ReferralSystem\Ui\Renderer
 */
class Amounts extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\ReferralSystem\Model\Transaction $transactionModel,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->transactionModel = $transactionModel;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws \Zend_Currency_Exception
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $store = $this->storeManager->getStore(
                    $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
                );
                $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

        		$referred_list = $this->transactionModel->getCollection()
        						->addFieldtoFilter ( 'customer_id', $item['entity_id']);
                $amount = 0;
        		foreach($referred_list as $value){
        		    if($value->getTransactionType()==2){
        		        $value['earned_amount'] = -$value['earned_amount'];
        		    }
        			$amount+=$value['earned_amount'];
        		}
                $item['amount'] = $currency->toCurrency(sprintf("%f", $amount));
            }
        }
        return $dataSource;
    }
}