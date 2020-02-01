<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\Export;

class ConvertToXml extends \Magento\Ui\Model\Export\ConvertToXml
{
    use ExportTrait;

    /**
     * @var BookmarkManagement
     */
    protected $bookmarkManagement;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Ui\Model\Export\MetadataProvider $metadataProvider,
        \Magento\Framework\Convert\ExcelFactory $excelFactory,
        \Magento\Ui\Model\Export\SearchResultIteratorFactory $iteratorFactory,
        \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagement

    ) {
        parent::__construct($filesystem, $filter, $metadataProvider, $excelFactory, $iteratorFactory);
        $this->bookmarkManagement = $bookmarkManagement;
    }

    public function getXmlFile()
    {
        $component = $this->filter->getComponent();

        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            'sales_order_grid'
        );
        $config = $bookmark ? $bookmark->getConfig() : null;

        $bookmarksCols = [];
        $availableProductDetails = [];
        if (is_array($config) && isset($config['current']) && isset($config['current']['columns'])) {
            $bookmarksCols = $config['current']['columns'];
        }

        foreach ($bookmarksCols as $key => $colItem) {
            if (!empty($colItem['visible'])
                && $colItem['visible'] == true
                && stripos($key, "amasty_ogrid_product") !== false) {
                $availableProductDetails[$key] = $colItem['amogrid_title'];
            }
        }


        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.xml';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();

        $component->getContext()->getDataProvider()->setLimit(0, 0);

        /** @var SearchResultInterface $searchResult */
        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();

        /** @var DocumentInterface[] $searchResultItems */
        $searchResultItems = $this->getDataProviderItems($component->getContext()->getDataProvider(), $availableProductDetails);//$searchResult->getItems();

        $this->prepareItems($component->getName(), $searchResultItems);

        /** @var SearchResultIterator $searchResultIterator */
        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItems]);

        /** @var Excel $excel */
        $excel = $this->excelFactory->create([
            'iterator' => $searchResultIterator,
            'rowCallback'=> [$this, 'getRowData'],
        ]);

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($this->metadataProvider->getHeaders($component));
        $excel->write($stream, $component->getName() . '.xml');

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

}
