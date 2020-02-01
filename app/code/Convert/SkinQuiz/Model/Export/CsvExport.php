<?php 

namespace Convert\SkinQuiz\Model\Export;

/**
 * 
 */
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;

class CsvExport extends \Magento\Ui\Model\Export\ConvertToCsv
{
	protected $directory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var int|null
     */
    protected $pageSize = null;
    protected $filter;

    function __construct(        
    	Filesystem $filesystem,
    	Filter $filter,
    	\Magento\Ui\Model\Export\MetadataProvider $metadataProvider,
    	$pageSize = 200
    )
    {
    	$this->filter = $filter;
    	$this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    	$this->metadataProvider = $metadataProvider;
    	$this->pageSize = $pageSize;
    }

    public function getFileCsv()
    {
    	$component = $this->filter->getComponent();

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
        $searchCriteria = $dataProvider->getSearchCriteria()->setCurrentPage($i)->setPageSize($this->pageSize);
        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
          $items = $dataProvider->getSearchResult()->getItems();
          foreach ($items as $item) 
          {

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