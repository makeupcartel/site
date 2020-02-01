<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Ui\Component\Listing\Column\Customer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use \Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class Actions
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
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
        CartRepositoryInterface $cartRepository,
        ModuleManager $moduleManager,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->cartRepository = $cartRepository;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
                $customerId = $item['customer_id'] ?? null;
                if ($customerId !== null) {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'customer/*/edit',
                            ['id' => $customerId, 'store' => $storeId]
                        ),
                        'label' => __('View Customer'),
                        'hidden' => false,
                    ];
                    $quoteId = $item['quote_id'] ?? null;
                    if ($quoteId !== null) {
                        $installedText = $this->moduleManager->isEnabled('Amasty_RequestQuote')
                            ? ''
                            : ' (' . __('Not Installed') . ')';
                        $item[$this->getData('name')]['create_quote'] = [
                            'href' => $this->urlBuilder->getUrl(
                                'amasty_quote/quote_create/exist',
                                ['quote_id' => $quoteId]
                            ),
                            'label' => __('Create Quote') . $installedText,
                            'hidden' => false,
                            'disabled' => !$this->moduleManager->isEnabled('Amasty_RequestQuote')
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
