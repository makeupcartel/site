<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */
namespace Amasty\Oaction\Model\Source;

use Magento\Framework\Json\DecoderInterface;

class Commands implements \Magento\Framework\Option\ArrayInterface
{
    const ORDERATTR_MODULE_NAME = 'Amasty_Orderattr';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $filesystem;

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        DecoderInterface $jsonDecoder
    ) {
        $this->moduleManager = $moduleManager;
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->jsonDecoder = $jsonDecoder;
    }

    public function toOptionArray()
    {
        $options = [];

        $types = [
            ''                           => '',
            'amasty_oaction_invoice'     => __('Invoice'),
            'amasty_oaction_invoiceship' => __('Invoice > Ship'),
            'amasty_oaction_ship'        => __('Ship'),
            'amasty_oaction_status'      => __('Change Status')
        ];
        if ($this->moduleManager->isEnabled(self::ORDERATTR_MODULE_NAME)
            && $this->getVersion() > '2.1.6'
        ) {
            $types['amasty_oaction_orderattr'] = __('Update Order Attributes');
        }
        foreach ($types as $value => $label) {
            $options[] =[
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    private function getVersion()
    {
        $version = '';
        $info = $this->getModuleInfo();
        if (isset($info['version'])) {
            $version = $info['version'];
        }

        return $version;
    }

    private function getModuleInfo()
    {
        $dir = $this->moduleReader->getModuleDir('', self::ORDERATTR_MODULE_NAME);
        $file = $dir . '/composer.json';

        $string = $this->filesystem->fileGetContents($file);
        $json = $this->jsonDecoder->decode($string);

        return $json;
    }
}
