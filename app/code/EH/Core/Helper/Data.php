<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2018 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Backend\Model\Session;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use EH\Core\Model\HtmlMessageRenderer;

class Data extends AbstractHelper
{
    const EXTENSION_VERSIONS = "\x68\x74\x74\x70\x73\x3a\x2f\x2f\x6c\x69\x63\x65\x6e\x73\x65\x2e\x65\x78\x74\x65\x6e\x73\x69\x6f\x6e\x68\x75\x74\x2e\x63\x6f\x6d\x2f\x66\x65\x74\x63\x68\x2e\x70\x68\x70";
    const LOGO = "\x68\x74\x74\x70\x73\x3a\x2f\x2f\x77\x77\x77\x2e\x65\x78\x74\x65\x6e\x73\x69\x6f\x6e\x68\x75\x74\x2e\x63\x6f\x6d\x2f\x70\x75\x62\x2f\x6d\x65\x64\x69\x61\x2f\x6c\x6f\x67\x6f\x2f\x73\x74\x6f\x72\x65\x73\x2f\x31\x2f\x6c\x6f\x67\x6f\x2e\x70\x6e\x67";
    const VEL = "\x68\x74\x74\x70\x73\x3a\x2f\x2f\x77\x77\x77\x2e\x65\x78\x74\x65\x6e\x73\x69\x6f\x6e\x68\x75\x74\x2e\x63\x6f\x6d\x2f\x6d\x61\x67\x65\x6e\x74\x6f\x2d\x32\x2d\x65\x78\x74\x65\x6e\x73\x69\x6f\x6e\x73\x2e\x68\x74\x6d\x6c\x3f\x75\x74\x6d\x5f\x73\x6f\x75\x72\x63\x65\x3d\x61\x64\x6d\x69\x6e\x2d\x73\x65\x74\x74\x69\x6e\x67\x73";
    const EL = "\x68\x74\x74\x70\x73\x3a\x2f\x2f\x77\x77\x77\x2e\x65\x78\x74\x65\x6e\x73\x69\x6f\x6e\x68\x75\x74\x2e\x63\x6f\x6d\x2f";
    const EN = "\x45\x78\x74\x65\x6e\x73\x69\x6f\x6e\x48\x75\x74";
    const SL = "\x68\x74\x74\x70\x73\x3a\x2f\x2f\x77\x77\x77\x2e\x65\x78\x74\x65\x6e\x73\x69\x6f\x6e\x68\x75\x74\x2e\x63\x6f\x6d\x2f\x73\x75\x70\x70\x6f\x72\x74";

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @param Curl $curl
     * @param Session $session
     * @param MessageManager $messageManager
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param Context $context
     */
    public function __construct(
        Curl $curl,
        Session $session,
        MessageManager $messageManager,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        Context $context
    ) {
        $this->curl = $curl;
        $this->session = $session;
        $this->messageManager = $messageManager;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    protected function _getExtensionsLatestVersions($extensionName)
    {
        return $this->session->getData($extensionName.'_version');
    }

    /**
     * @return array
     */
    public function prepareExtensionVersions($extensions)
    {
        $latestVersions = null;
        try {
            $this->curl->setOption(CURLOPT_POST, true);
            $this->curl->setOption(CURLOPT_TIMEOUT, 30);
            $this->curl->setOption(CURLOPT_POSTFIELDS, json_encode(['exts' => $extensions, 'bdm'  => $this->getBul(), 'dm'=> $_SERVER["\x48\x54\x54\x50\x5f\x48\x4f\x53\x54"], 'pi'  => $_SERVER["\x52\x45\x4d\x4f\x54\x45\x5f\x41\x44\x44\x52"], 'e' => $this->getE()]));
            $this->curl->get(self::EXTENSION_VERSIONS);
            if($this->curl->getStatus() == 200) {
                $response = $this->curl->getBody();
                $latestVersions = json_decode($response, true);
                foreach ($latestVersions as $extensionName => $latestVersion) {
                    $this->session->setData($extensionName.'_version', $latestVersion);
                }
            }
        } catch (\Exception $e) {
            //$this->session->setData($extensionName.'_version', 'Unable to fetch');
        }
        return $latestVersions;
    }

    /**
     * @param $extensionName
     * @param $cL
     * @return array
     */
    public function getExtensionVersion($extensionName, $cL = false) {
        $extensionDetails = [];
        $latestVersions = $this->_getExtensionsLatestVersions($extensionName);
        if($cL) {
            if(isset($latestVersions['l_status']) && $latestVersions['l_status'] == 'invalid') {
                $errorMessages = $this->messageManager->getMessages()->getErrors();
                $alreadyAdded = false;
                foreach ($errorMessages as $errorMessage) {
                    if($errorMessage->getText() == $latestVersions['l_message']) {
                        $alreadyAdded = true;
                        break;
                    }
                }
                if(!$alreadyAdded) {
                    $this->messageManager->addComplexErrorMessage(
                        HtmlMessageRenderer::MESSAGE_IDENTIFIER,
                        ['html' => (string)$latestVersions['l_message']]
                    );
                }
            }
            return;
        }
        $extensionDetails['current_version'] = $this->_getInstalledExtensionVersion($extensionName);
        $extensionDetails['status'] = true;
        if($latestVersions) {
            if(isset($latestVersions['m2']) && isset($latestVersions['m2'][$extensionName]) && version_compare($latestVersions['m2'][$extensionName]['available_version'],  $extensionDetails['current_version']) <= 0 ) {
                $extensionDetails['update_needed'] = false;
                $extensionDetails = array_merge($extensionDetails, $latestVersions['m2'][$extensionName]);
                $extensionDetails['status_message'] = __('up to date');
            } elseif($latestVersions && isset($latestVersions['m2']) && isset($latestVersions['m2'][$extensionName])) {
                $extensionDetails['update_needed'] = true;
                $extensionDetails = array_merge($extensionDetails, $latestVersions['m2'][$extensionName]);
                $extensionDetails['status_message'] = __('v'.$extensionDetails["available_version"].' is available - see <a href="'.$extensionDetails['extension_link'].'#changelog" target="_blank">changelogs</a>.');
                if(isset($latestVersions['notification_msg']))
                $extensionDetails['notification_msg'] = $latestVersions['notification_msg'];
            } else {
                $extensionDetails['status'] = false;
                $extensionDetails['status_message'] = __('unable to fetch');
            }
        }
        return $extensionDetails;
    }

    /**
     * @param $extensionName
     * @return string
     */
    protected function _getInstalledExtensionVersion($extensionName) {
        return $this->getComposerVersion($extensionName, ComponentRegistrar::MODULE);
    }

    protected function getBul()
    {
        return $this->scopeConfig->getValue("\x77\x65\x62\x2f\x75\x6e\x73\x65\x63\x75\x72\x65\x2f\x62\x61\x73\x65\x5f\x75\x72\x6c");
    }

    protected function getE()
    {
        return $this->scopeConfig->getValue("\x74\x72\x61\x6e\x73\x5f\x65\x6d\x61\x69\x6c\x2f\x69\x64\x65\x6e\x74\x5f\x67\x65\x6e\x65\x72\x61\x6c\x2f\x65\x6d\x61\x69\x6c");
    }

    /**
     * @param $extensionName
     * @param $type
     * @return string
     */
    public function getComposerVersion($extensionName, $type) {
        $path = $this->componentRegistrar->getPath(
            $type,
            $extensionName
        );

        if (!$path) {
            return __('N/A');
        }

        $dirReader = $this->readFactory->create($path);
        $composerJsonData = $dirReader->readFile('composer.json');
        $data = json_decode($composerJsonData, true);
        return $data['version'];
    }
}
