<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\Export;

class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
    use ExportTrait;

    /**
     * @var \Magento\Ui\Api\BookmarkManagementInterface BookmarkManagement
     */
    protected $bookmarkManagement;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Ui\Model\Export\MetadataProvider $metadataProvider,
        \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagement,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($filesystem, $filter, $metadataProvider);
        $this->bookmarkManagement = $bookmarkManagement;
    }

    /**
     * Returns CSV file
     *
     * @return array
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            'sales_order_grid'
        );
        $config = $bookmark ? $bookmark->getConfig() : null;

        $bookmarksCols = [];
        $availableProductDetails = [];
        if (is_array($config) && isset($config['current']['columns'])) {
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
        $file = 'export/'. $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
            $items = $this->getDataProviderItems($dataProvider, $availableProductDetails);

            foreach ($items as $idx => $item) {
                $this->metadataProvider->convertDate($item, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}
