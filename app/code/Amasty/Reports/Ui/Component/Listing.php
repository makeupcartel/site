<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Ui\Component;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Listing
 * @package Amasty\Reports\Ui\Component
 */
class Listing extends \Magento\Ui\Component\Listing
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    /**
     * @return string
     */
    public function render()
    {
        $result = parent::render();
        if (is_string($result)) {
            $result = $this->decoder->decode($result);
            $result = $this->castToColumnFormats($result);
            $result = $this->encoder->encode($result);
        }

        return $result;
    }

    /**
     * @param array $result
     *
     * @return array
     */
    private function castToColumnFormats($result)
    {
        $columnsComponent = $this->getComponent('amreports_sales_overview_columns');
        $dataSource = [
            'data' => [
                'items' => [
                    $result['totals']
                ]
            ]
        ];

        foreach ($columnsComponent->getChildComponents() as $component) {
            $dataSource = $component->prepareDataSource($dataSource);
        }

        $result['totals'] = $dataSource['data']['items'][0];

        return $result;
    }
}
