<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Ui\Component;

/**
 * Class ExportButton
 */
class ExportButton extends \Magento\Ui\Component\ExportButton
{
    /**
     * Change url on export button
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                switch ($option['value']) {
                    case 'csv':
                        $option['url'] = 'amasty_ogrid/export/gridToCsv';
                        break;

                    case 'xml':
                        $option['url'] = 'amasty_ogrid/export/gridToXml';
                        break;
                }
                $options[] = $option;
            }
            $config['options'] = $options;
        }
        $this->setData('config', $config);
        parent::prepare();
    }
}
