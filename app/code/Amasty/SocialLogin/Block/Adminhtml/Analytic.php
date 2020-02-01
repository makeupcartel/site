<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Block\Adminhtml;

class Analytic extends \Magento\Backend\Block\Template
{
    const MAX_TOP_SOCIALS = 3;

    /**
     * @var bool
     */
    private $isSocialLoginExists = false;

    /**
     * @var array
     */
    private $topSocialLogin = [];

    /**
     * @var array
     */
    private $allSocialLogin = [];

    /**
     * @var array
     */
    private $otherConnection = [];

    /**
     * @var \Amasty\SocialLogin\Model\ResourceModel\Social\AnalyticCollectionFactory
     */
    private $analyticCollectionFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\SocialLogin\Model\ResourceModel\Social\AnalyticCollectionFactory $analyticCollectionFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->analyticCollectionFactory = $analyticCollectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->loadSocialData();
    }

    private function loadSocialData()
    {
        $data = $this->analyticCollectionFactory->create()->getSocialLoginData();
        $total = count($data);
        if ($total) {
            $this->isSocialLoginExists = true;

            $typeArray = $this->convertData($data);
            usort($typeArray, [$this, 'sortSocials']);
            $this->divideDataByGroups($typeArray, $total);
        }
    }

    /**
     * @return bool
     */
    public function isSocialLoginExists()
    {
        return $this->isSocialLoginExists;
    }

    /**
     * @return array
     */
    public function getTopSocialLogin()
    {
        return $this->topSocialLogin;
    }

    /**
     * @return array
     */
    public function getOtherConnection()
    {
        return $this->otherConnection;
    }

    /**
     * @param $data
     *
     * @return array
     */
    private function convertData($data)
    {
        $typeArray = [];
        foreach ($data as $loginItem) {
            /** @var \Amasty\SocialLogin\Model\Social $loginItem */
            $type = $loginItem->getType();
            if (array_key_exists($type, $typeArray)) {
                $typeArray[$type]['count'] += 1;
                $typeArray[$type]['amount'] += $loginItem->getAmount();
                $typeArray[$type]['items'] += $loginItem->getItems();
            } else {
                $typeArray[$type] = [
                    'count'  => 1,
                    'amount' => $loginItem->getAmount(),
                    'type'   => $type,
                    'items'  => $loginItem->getItems()
                ];
            }
        }

        return $typeArray;
    }

    private function divideDataByGroups($typeArray, $total)
    {
        $counter = 1;
        foreach ($typeArray as $name => $type) {
            $itemData = [
                'key'     => $type['type'],
                'count'   => $type['count'],
                'percent' => round($type['count'] / $total * 100) . '%',
                'amount'  => $this->formatPrice($type['amount']),
                'items'   => $type['items']
            ];

            $this->allSocialLogin[] = $itemData;
            if ($counter <= self::MAX_TOP_SOCIALS) {
                $this->topSocialLogin[] = $itemData;
                $counter++;
            } else {
                $this->otherConnection[] = $itemData;
            }
        }
    }

    /**
     * @param float $price
     *
     * @return float
     */
    private function formatPrice($price)
    {
        return $this->priceCurrency->format($price);
    }

    /**
     * @param array $first
     * @param array $second
     *
     * @return bool
     */
    public function sortSocials($first, $second)
    {
        return $first['count'] < $second['count'];
    }

    /**
     * @return string
     */
    public function getPieData()
    {
        $result = [];
        foreach ($this->allSocialLogin as $item) {
            $result[] = [
                'label' => ucfirst($item['key']),
                'value' => $item['count']
            ];
        }

        return $this->jsonEncoder->encode($result);
    }
}
