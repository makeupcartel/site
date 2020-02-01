<?php
namespace Convert\CustomerContent\Model;

use Convert\CustomerContent\Model\ResourceModel\Content\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class ContentDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * ContentDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $contentCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $contentCollectionFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $contentCollectionFactory->create();
        $this->_assetRepo = $assetRepo;
        $this->storeManager = $storeManager;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        $this->_loadedData = [];
        foreach ($items as $data) {
            $this->_loadedData[$data->getId()] = $data->getData();
            if ($data['file_path']) {
                $fileUrl = $this->getMediaUrl() . $data['file_path'];
                $m['file_path'][0]['name'] = $data['file_path'];
                $m['file_path'][0]['url'] = @getimagesize($fileUrl) ? $fileUrl : $this->_assetRepo->getUrl("Convert_CustomerContent::images/file.png");
                $fullData = $this->_loadedData;
                $this->_loadedData[$data->getId()] = array_merge($fullData[$data->getId()], $m);
            }
            if ($data['thumbnail']) {
                $fileUrl = $this->getMediaUrl() . $data['thumbnail'];
                $m['thumbnail'][0]['name'] = $data['thumbnail'];
                $m['thumbnail'][0]['url'] = $fileUrl;
                $fullData = $this->_loadedData;
                $this->_loadedData[$data->getId()] = array_merge($fullData[$data->getId()], $m);
            }
        }
        return $this->_loadedData;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }
}
