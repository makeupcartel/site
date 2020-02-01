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
 * Class Name
 * @package Amasty\Reports\Ui\Component\Listing\Column\Product
 */
class Name extends Column
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
                if (isset($item['id_field_name']) && isset($item['product_id'])) {
                    $url = $this->urlBuilder->getUrl(
                        'catalog/product/edit',
                        [
                            'id' => $item['product_id']
                        ]
                    );
                    //@codingStandardsIgnoreStart
                    $item[$this->getData('name')] = sprintf(
                        '<a href="%s" title="%s" target="_blank">%s</a>',
                        $url,
                        __('View Product'),
                        $item[$this->getData('name')]
                    );
                    //@codingStandardsIgnoreEnd
                }
            }
        }

        return $dataSource;
    }
}
