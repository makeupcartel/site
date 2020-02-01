<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model;

use Magento\Framework\Model\AbstractModel;

class Method extends AbstractModel
{
    const MEDIA_MODULE_DIRECTORY = 'amtable';

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploadFactory;
    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    protected function _construct()
    {
        $this->_init('Amasty\ShippingTableRates\Model\ResourceModel\Method');
    }

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploadFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context, $coreRegistry);
        $this->adapterFactory = $adapterFactory;
        $this->uploadFactory = $uploadFactory;
        $this->filesystem = $filesystem;
    }

    public function massChangeStatus($ids, $status)
    {
        foreach ($ids as $id) {
            $model = $this->load($id);
            $model->setIsActive($status);
            $model->save();
        }
        return $this;
    }

    public function getFreeTypes()
    {
        $result = array();
        $freeTypesString = trim($this->getData('free_types'),',');
        if ($freeTypesString) {
            $result = explode(',', $freeTypesString);
        }
        return $result;
    }

    public function joinLabel($modelId)
    {
        $this->getSelect()->joinLeft(
            ['label' => $this->getTable('amasty_method_label')],
            'main_table.id = label.method_id'
        )->where(
            'main_table.id=?',
            $modelId
        );

        return $this;
    }

    /**
     * @param $file
     * @return string
     */
    public function saveImage($file)
    {
        $uploader = $this->uploadFactory->create(['fileId' => $file]);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);
        $mediaDirectory = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        );
        $result = $uploader->save(
            $mediaDirectory->getAbsolutePath(self::MEDIA_MODULE_DIRECTORY)
        );
        $img = self::MEDIA_MODULE_DIRECTORY . $result['file'];

        return $img;
    }
}
