<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Ui\Component\Listing\Column\Product;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 * @package Amasty\Reports\Ui\Component\Listing\Column\Product
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        DataPersistorInterface $dataPersistor,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['id_field_name'])) {
                    $url = $this->urlBuilder->getUrl(
                        'amasty_reports/report_catalog/byProduct',
                        [
                            'customer_group_id' => $item['customer_group_id']
                        ]
                    );
                    $url .= '?';
                    foreach ($this->getContext()->getRequestParam('amreports', []) as $key => $value) {
                        if ($value && $key != 'rule') {
                            $url .= 'amreports[' . $key . ']=' . $value . '&';
                        }
                    }
                    $url = rtrim($url, '&');

                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $url,
                            'label' => __('Show More')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
