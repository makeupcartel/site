<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Megamenu\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * Class ExportButton
 */
class ExportButton extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'exportButton';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        
        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                if ($option['value'] == 'xml') {
                    $option['label'] = __('Export to XML');
                    $option['url'] = $this->urlBuilder->getUrl('megamenu/sampleimport/export');
                    $options[] = $option;
                }
            }
            $config['options'] = $options;
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}
