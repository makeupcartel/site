<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Messages section
 */
class Messages implements SectionSourceInterface
{
    const VAR_ENABLED = 'messages/display_notification';

    const VAR_TEXT = 'messages/notification_text';

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $promoHelper;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        \Amasty\Promo\Model\Config $config,
        \Amasty\Promo\Helper\Data $promoHelper,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->promoHelper = $promoHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getNoticeHtml()
    {
        $placeholders = [
            '{url checkout/cart}' => $this->urlBuilder->getUrl('checkout/cart')
        ];

        $noticeHtml = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->config->getScopeValue(self::VAR_TEXT)
        );

        return $noticeHtml;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (int)$this->config->getScopeValue(self::VAR_ENABLED) === 1;
    }

    /**
     * @return int
     */
    public function getNewItemsCount()
    {
        $count = 0;
        if (($items = $this->promoHelper->getNewItems())
            && $items instanceof \Magento\Catalog\Model\ResourceModel\Product\Collection
        ) {
            $count = $items->getSize();
        }

        return $count;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        if ($this->isEnabled() && $this->getNewItemsCount()) {
            return [
                'messages' => [
                    'notice' => [
                        'text' => $this->getNoticeHtml(),
                        'type' => 'notice'
                    ]
                ]
            ];
        }
        return ['messages' => []];
    }
}
