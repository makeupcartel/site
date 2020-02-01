<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Cron;

use Amasty\Reports\Model\Abandoned\Cart;
use Amasty\Reports\Model\Abandoned\CartFactory;
use Amasty\Reports\Model\ResourceModel\Abandoned\Cart as ResourceCart;
use Amasty\Reports\Model\ResourceModel\Quote\Collection;
use Amasty\Reports\Model\ResourceModel\Quote\CollectionFactory;
use Amasty\Reports\Model\Source\Status;
use Magento\Framework\Flag;
use Magento\Framework\FlagFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

/**
 * Class Abandoned
 * @package Amasty\Reports\Cron
 */
class Abandoned
{
    const LAST_EXECUTED_CODE = 'amasty_report_abandoned_last_executed';

    /**
     * @var int
     */
    private $actualGap = 60000;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var int
     */
    private $currentExecution;

    /**
     * @var int
     */
    private $lastExecution;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @var Flag
     */
    private $flagData;

    /**
     * @var CartFactory
     */
    private $cartFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $ignoredTypes = [
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        \Magento\Bundle\Model\Product\Type::TYPE_CODE
    ];

    public function __construct(
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        Date $date,
        FlagFactory $flagFactory,
        CartFactory $cartFactory,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->flagFactory = $flagFactory;
        $this->cartFactory = $cartFactory;
        $this->logger = $logger;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var Quote $quote */
        foreach ($this->getQuoteCollection() as $quote) {
            try {
                /** @var Cart $abandonedCart */
                $abandonedCart = $this->cartFactory->create();
                $abandonedCart->loadByQuoteId($quote->getId());
                if ($abandonedCart->getId() && $abandonedCart->getData(ResourceCart::STATUS) == Status::COMPLETE) {
                    continue;
                }
                $productIds = implode(',', $this->getProductIds($quote));
                $customerName = $this->getCutomerName($quote);
                $status = $quote->getItemsCollection()->getSize() ? Status::PROCESSING : Status::EMPTY_CART;
                $abandonedCart->addData(
                    [
                        ResourceCart::QUOTE_ID      => $quote->getId(),
                        ResourceCart::STORE_ID      => $quote->getStoreId(),
                        ResourceCart::CREATED_AT    => $quote->getCreatedAt(),
                        ResourceCart::ITEMS_QTY     => $quote->getItemsQty(),
                        ResourceCart::PRODUCTS      => $productIds,
                        ResourceCart::GRAND_TOTAL   => $quote->getBaseGrandTotal(),
                        ResourceCart::CUSTOMER_NAME => trim($customerName),
                        ResourceCart::STATUS        => $status,
                        ResourceCart::COUPON_CODE   => $quote->getCouponCode(),
                        ResourceCart::CUSTOMER_ID   => $quote->getCustomerId(),
                    ]
                );
                $abandonedCart->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @param Quote $quote
     * @return array
     */
    private function getProductIds($quote)
    {
        $productIds = [];
        /** @var Item $item */
        foreach ($quote->getItemsCollection() as $item) {
            if (!in_array($item->getProduct()->getTypeId(), $this->ignoredTypes)
                && !in_array($item->getProductId(), $productIds)
            ) {
                $productIds[] = $item->getProductId();
            }
        }

        return $productIds;
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getQuoteCollection()
    {
        return $this->collectionFactory->create()->addIsActiveFilter()->addDateFilter(
            $this->dateTime->formatDate($this->getLastExecution()),
            $this->dateTime->formatDate($this->getCurrentExecution())
        );
    }

    /**
     * @param Quote $quote
     *
     * @return string
     */
    private function getCutomerName($quote)
    {
        return ($quote->getCustomerPrefix() ? $quote->getCustomerPrefix() . ' ' : '')
            . $quote->getCustomerFirstname()
            . ($quote->getCustomerMiddlename() ? ' ' . $quote->getCustomerMiddlename() : '')
            . ' ' . $quote->getCustomerLastname()
            . ($quote->getCustomerSuffix() ? ' ' . $quote->getCustomerSuffix() : '');
    }

    /**
     * @return int
     */
    private function getCurrentExecution()
    {
        if ($this->currentExecution === null) {
            $this->currentExecution = $this->date->gmtTimestamp();
        }

        return $this->currentExecution;
    }

    /**
     * @return int|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getLastExecution()
    {
        if ($this->lastExecution === null) {
            $flag = $this->getFlag()->loadSelf();
            $this->lastExecution = (string)$flag->getFlagData();

            if (empty($this->lastExecution)) {
                $this->lastExecution = $this->date->gmtTimestamp() - $this->actualGap;
            }

            $flag->setFlagData($this->getCurrentExecution());
            $flag->save();
        }

        return $this->lastExecution;
    }

    /**
     * @return Flag
     */
    protected function getFlag()
    {
        if ($this->flagData === null) {
            $this->flagData = $this->flagFactory->create(['data' => ['flag_code' => self::LAST_EXECUTED_CODE]]);
        }

        return $this->flagData;
    }
}
