<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\Currency;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    const DATE_FROM_FLAG = 'amasty_reports_from_date';
    const DATE_TO_FLAG = 'amasty_reports_to_date';

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * Wrapped component
     *
     * @var \Magento\Ui\Component\Form\Element\DataType\Date
     */
    private $wrappedComponent;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\FlagFactory
     */
    private $flagFactory;

    /**
     * @var \Amasty\Reports\Model\Source\Country
     */
    private $sourceContry;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Session $session,
        \Magento\Ui\Component\Form\Element\DataType\Date $wrappedComponent,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\PriceCurrency $priceCurrency,
        \Magento\Framework\FlagFactory $flagFactory,
        \Amasty\Reports\Model\Source\Country $sourceContry
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->wrappedComponent = $wrappedComponent;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->flagFactory = $flagFactory;
        $this->sourceContry = $sourceContry;
    }

    /**
     * @return int
     */
    public function getDefaultFromDate()
    {
        try {
            $date = $this->getFlag(self::DATE_FROM_FLAG)->loadSelf()->getFlagData() ?: strtotime('-7 day');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $date = 0;
        }

        return (int) $date;
    }

    /**
     * @return int
     */
    public function getDefaultToDate()
    {
        try {
            $date = $this->getFlag(self::DATE_TO_FLAG)->loadSelf()->getFlagData() ?: time();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $date = 0;
        }

        return (int) $date;
    }

    /**
     * @param string $code
     * @return \Magento\Framework\Flag
     */
    private function getFlag($code)
    {
        return $this->flagFactory->create([
            'data' => [
                'flag_code' => $code
            ]
        ]);
    }

    /**
     * @return int
     */
    public function getCurrentStoreId()
    {
        $params = $this->_getRequest()->getParam('amreports');
        $storeId = $this->_getRequest()->getParam(
            'store',
            $params['store'] ?? null
        );
        if ($storeId === null) {
            $storeId = $this->session->getAmreportsStore();
        }

        return (int)$storeId;
    }

    /**
     * @param $store
     * @return mixed
     */
    public function setCurrentStore($store)
    {
        return $this->session->setAmreportsStore($store);
    }

    /**
     * Getting time according to locale
     *
     * @param string|Date $date
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return string
     */
    public function getDateForLocale($date, $hour = 0, $minute = 0, $second = 0)
    {
        $skipTimeZoneConversion = $this->scopeConfig->getValue(
            'config/skipTimeZoneConversion',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $this->wrappedComponent->convertDate(
            $date,
            $hour,
            $minute,
            $second,
            !$skipTimeZoneConversion
        )->format('Y-m-d H:i:s');
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return $this->scopeConfig->getValue('amasty_reports/general/reports_statuses');
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function getCurrencyCode()
    {
        $params = $this->_request->getParam('amreports', []);
        $storeId = isset($params['store_id']) ? $params['store_id'] : \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $store = $this->storeManager->getStore($storeId);

        return $store->getBaseCurrencyCode();
    }

    /**
     * @param $price
     * @return float|string
     */
    public function convertPrice($price)
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            false,
            null,
            $this->getCurrentStoreId(),
            $this->getDisplayCurrency()
        );
    }

    /**
     * @return string
     */
    public function getDisplayCurrency()
    {
        return $this->scopeConfig->getValue(
            Currency::XML_PATH_CURRENCY_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            $this->getCurrentStoreId()
        );
    }

    /**
     * @return \Amasty\Reports\Model\Source\Country
     */
    public function getCountryDataSource()
    {
        return $this->sourceContry;
    }
}
