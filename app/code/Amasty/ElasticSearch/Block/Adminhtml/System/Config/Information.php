<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;

class Information extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2:elastic_search';

    /**
     * @var array
     */
    private $enemyExtensions = [];

    /**
     * @var string
     */
    private $content;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleList = $moduleList;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $this->setContent(__('Please update Amasty Base module. Re-upload it and replace all the files.'));

        $this->_eventManager->dispatch(
            'amasty_base_add_information_content',
            ['block' => $this]
        );

        $html .= $this->getContent();
        $html .= $this->_getFooterHtml($element);

        $html = str_replace(
            'amasty_information]" type="hidden" value="0"',
            'amasty_information]" type="hidden" value="1"',
            $html
        );
        $html = preg_replace('(onclick=\"Fieldset.toggleCollapse.*?\")', '', $html);

        return $html;
    }

    /**
     * @return string
     */
    public function getUserGuide()
    {
        return $this->userGuide;
    }

    public function getAdditionalModuleContent()
    {
        $result = '';
        if (!class_exists(\Elasticsearch\ClientBuilder::class)) {
            $message = [
                'type' => 'message-error',
                'text' => __('Elasticsearch package is not installed. Please, run the following command in the SSH: composer require elasticsearch/elasticsearch ~5.1')
            ];

            if ($this->getBaseVersion() < '1.3.4') {
                return $message['text'];
            }

            $result = [];
            $result[] = $message;
        }

        return $result;
    }

    /**
     * @param string $userGuide
     */
    public function setUserGuide($userGuide)
    {
        $this->userGuide = $userGuide;
    }

    /**
     * @return array
     */
    public function getEnemyExtensions()
    {
        return $this->enemyExtensions;
    }

    /**
     * @param array $enemyExtensions
     */
    public function setEnemyExtensions($enemyExtensions)
    {
        $this->enemyExtensions = $enemyExtensions;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    private function getBaseVersion()
    {
        $version = '';
        if (isset($this->moduleList->getOne('Amasty_Base')['setup_version'])) {
            $version = $this->moduleList->getOne('Amasty_Base')['setup_version'];
        }

        return $version;
    }
}
