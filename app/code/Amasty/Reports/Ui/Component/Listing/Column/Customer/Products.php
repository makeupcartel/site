<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */



namespace Amasty\Reports\Ui\Component\Listing\Column\Customer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Products
 */
class Products extends Column
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $productIds = explode(
                    ',',
                    $item[$this->getData('name')]
                );
                $item[$this->getData('name')] = $this->getProductsContent($productIds);
            }
        }

        return $dataSource;
    }

    /**
     * @param array $productIds
     *
     * @return string
     */
    private function getProductsContent($productIds)
    {
        $content = '';
        foreach ($productIds as $productId) {
            try {
                $product = $this->productRepository->getById($productId);
                // @codingStandardsIgnoreStart
                $content .= '<p class="product-group">' . $product->getName() . '<br/>' . str_repeat('&nbsp;', 3) . __('SKU') . ':'
                    . $product->getSku() . '<br/></p>';
                // @codingStandardsIgnoreEnd
            } catch (NoSuchEntityException $entityException) {
                continue;
            }
        }

        return $content;
    }
}
