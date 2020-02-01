<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Json\EncoderInterface;

class ConnectionChecker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Amasty_ElasticSearch::system/config/connection.phtml');

        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'json_config' => $this->generateButtonConfig($element),
                'html_id' => $element->getHtmlId()
            ]
        );

        return $this->_toHtml();
    }

    /**
     * @return array
     */
    private function getFieldMapping()
    {
        return [
            'hostname' => 'server_hostname',
            'port' => 'server_port',
            'index' => 'index_prefix',
            'enableAuth' => 'enable_auth',
            'username' => 'username',
            'password' => 'password',
            'timeout' => 'server_timeout',
            'engine' => 'engine'
        ];
    }

    /**
     * @param $element
     * @return mixed
     */
    private function generateButtonConfig(AbstractElement $element)
    {
        $result = [
            'url' =>  $this->_urlBuilder->getUrl('amasty_elastic/config_checker/connection'),
            'elementId' => $element->getHtmlId(),
            'successText' => __('Successful! Test again?'),
            'failedText' => __('Connection failed! Test again?'),
            'fieldMapping' => $this->getFieldMapping(),
            'validation' => [],
        ];

        return $this->jsonEncoder->encode($result);
    }
}
