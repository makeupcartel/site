<?php

namespace Unirgy\SimpleUp\Helper;

use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Simplexml\Config;
use Unirgy\SimpleUp\Model\Module;
use Zend\Json\Json;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class Data extends AbstractHelper
{
    /**
     * @var RequestInterface
     */
    protected $_requestInterface;

    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @var WriteInterface
     */
    protected $_directoryWrite;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var Encryptor
     */
    protected $_encryptor;

    /**
     * @var Module
     */
    protected $_modelModule;

    protected $_ftpPassword;

    protected $_ftpDirMode = 0775;

    protected $_ftpFileMode = 0664;

    /**
     * @var CacheManager
     */
    protected $_cacheManager;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $modulesList;

    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        WriteFactory $writeFactory,
        Filesystem $filesystem,
        EncryptorInterface $encryptorInterface,
        CacheManager $cacheManager,
        Module $module
    ) {
        parent::__construct($context);
        $this->_directoryList = $directoryList;
        $this->_directoryWrite = $writeFactory;
        $this->_filesystem = $filesystem;
        $this->_encryptor = $encryptorInterface;
        $this->_modelModule = $module;
        $this->_cacheManager = $cacheManager;

//        $this->scopeConfig->loadModulesConfiguration('usimpleup.xml', Mage::getConfig());
        $this->_ftpPassword = $this->_request->getPost('ftp_password');
    }

    public function download($uri)
    {
        $parsed = parse_url($uri);
        if (empty($parsed['host']) || !preg_match('#(^|\.)unirgy\.com$#', $parsed['host'])) {
            throw new Exception(__('Invalid download URL: %1', $uri));
        }
        /** @var $dlDir WriteInterface $dlDir */
        $dlDir = $this->_directoryWrite->create($this->_directoryList->getPath('var') . '/usimpleup/download');
        if (!$dlDir->isExist()) {
            try {
                $dlDir->create();
            } catch (Exception $exception) {
                throw new FileSystemException(__('Error creating folder: %1', $dlDir));
            }
        }

        $filePath = $dlDir->getAbsolutePath('/' . basename($parsed['path']));
        $fd = fopen($filePath, 'wb');

        $uri .= (strpos($uri, '?') === false ? '?' : '&') . 'php=' . PHP_VERSION;
        if (function_exists('ioncube_loader_version')) {
            $uri .= '&ioncube=' . ioncube_loader_version();
        } elseif (function_exists('sg_load')) {
            $uri .= '&sg=1';
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $uri,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_FILE => $fd,
        ]);
        if ((bool)$this->scopeConfig->isSetFlag('usimpleup/general/verify_ssl')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            #curl_setopt($curl, CURLOPT_CAINFO, dirname( __DIR__ ) . '/etc/gd_bundle-g2-g1.crt');
            curl_setopt($ch, CURLOPT_CAINFO, dirname( __DIR__ ) . '/ssl/cacert.pem');
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        if (curl_exec($ch) === false) {
            $error = __('Error while downloading file: %1', curl_error($ch));
            curl_close($ch);
            fclose($fd);
            throw new Exception($error);
        }
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            $error = __('File not found or error while downloading: %1', $uri);
            curl_close($ch);
            fclose($fd);
            throw new Exception($error);
        }
        curl_close($ch);
        fclose($fd);

        return $filePath;
    }

    public function install($uri, $filePath)
    {
        $tempDir = $this->_directoryList->getPath('var') . '/usimpleup/unpacked' . '/' . basename($filePath);
        $this->_directoryWrite->create($tempDir)->create();

        $this->unarchive($filePath, $tempDir);
        $this->registerModulesFromDir($uri, $tempDir);

        $useFtp = $this->scopeConfig->getValue('usimpleup/ftp/active');
        if ($useFtp) {
            $errors = $this->ftpUpload($tempDir);
            if ($errors) {
                $logDir = $this->_directoryList->getPath('var') . '/usimpleup/log' . '/' . basename($filePath);
                $this->_directoryWrite->create($logDir)->create();

                $fd = fopen($logDir . '/errors.log', 'a+');
                foreach ($errors as $error) {
                    fwrite($fd, date('Y-m-d H:i:s') . ' ' . $error . "\n");
                }
                fclose($fd);
                throw new Exception(__('Errors during FTP upload, see this log file: %s',
                    'usimpleup/log/' . basename($filePath) . '/errors.log'));
            }
        } else {
            $this->unarchive($filePath, $this->_directoryList->getRoot());
        }

        return $this;
    }

    public function unarchive($filePath, $target)
    {
        switch (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
            case 'zip':
                $this->unzip($filePath, $target);
                break;
            default:
                throw new Exception(__('Unknown archive format'));
        }
    }

    public function unzip($filePath, $target)
    {
        if (!extension_loaded('zip')) {
            throw new Exception(__('Zip PHP extension is not installed'));
        }
        $zip = new \ZipArchive();
        if (!$zip->open($filePath)) {
            throw new Exception(__('Invalid or corrupted zip file'));
        }
        if (!$zip->extractTo($target)) {
            $zip->close();
            throw new Exception(__('Errors during unpacking zip file. Please check destination write permissions: %s',
                $target));
        }
        $zip->close();
    }

    public function ftpUpload($from)
    {
        if (!extension_loaded('ftp')) {
            throw new Exception(__('FTP PHP extension is not installed'));
        }
        $conf = $this->scopeConfig->getValue('usimpleup/ftp');
        if (!($conn = ftp_connect($conf['host'], $conf['port']))) {
            throw new Exception(__('Could not connect to FTP host'));
        }
        $password = $this->_ftpPassword ?: $this->_encryptor->decrypt($conf['password']);
        if (!@ftp_login($conn, $conf['user'], $password)) {
            ftp_close($conn);
            throw new Exception(__('Could not login to FTP host'));
        }
        if (!@ftp_chdir($conn, $conf['path'])) {
            ftp_close($conn);
            throw new Exception(__('Could not navigate to FTP Magento base path'));
        }

        $errors = $this->ftpUploadDir($conn, $from . '/');

        ftp_close($conn);

        return $errors;
    }

    public function ftpUploadDir($conn, $source, $ftpPath = '')
    {
        $errors = [];
        $dir = opendir($source);
        while ($file = readdir($dir)) {
            if ($file === '.' || $file === "..") {
                continue;
            }
            if (!is_dir($source . $file)) {
                if (@ftp_put($conn, $file, $source . $file, FTP_BINARY)) {
                    // all is good
                    #ftp_chmod($conn, $this->_ftpFileMode, $file);
                } else {
                    $errors[] = ftp_pwd($conn) . '/' . $file;
                }
                continue;
            }
            if (@ftp_chdir($conn, $file)) {
                // all is good
            } elseif (@ftp_mkdir($conn, $file)) {
                ftp_chmod($conn, $this->_ftpDirMode, $file);
                ftp_chdir($conn, $file);
            } else {
                $errors[] = ftp_pwd($conn) . '/' . $file . '/';
                continue;
            }
            $errors += $this->ftpUploadDir($conn, $source . $file . '/', $ftpPath . $file . '/');
            ftp_chdir($conn, '..');
        }
        return $errors;
    }

    public function registerModulesFromDir($uri, $dir)
    {
        $configFiles = glob($dir . '/app/code/*/*/etc/config.xml');
        if (!$configFiles) {
            throw new \RuntimeException('Could not find module configuration files');
        }
        foreach ($configFiles as $file) {
            $dir = dirname($file);
            $config = new Config($file);
            if (is_file($dir . '/module.xml')) {
                $modConfig = new Config($dir . '/module.xml');
                $config->extend($modConfig);
            }
            if (!$config->getNode('module')) {
                continue;
            }

            $node = $config->getNode('module');
            $modName = (string)$node['name'];
            $modConf = $config->getNode('default/' . $modName);
//            foreach ($node->children() as $modName => $modConf) {
            if (!$modConf || !isset($modConf->usimpleup) || !isset($modConf->usimpleup['remote'])) {
                continue;
            }
            $this->_modelModule->load($modName, 'module_name')
                ->setModuleName($modName)
                ->setDownloadUri($uri)
                ->setLastDownloaded(self::now())
                ->setLastVersion((string)$node['setup_version'])
                ->save();
            /*
            $email = $this->_appConfigScopeConfigInterface->getValue('trans_email/ident_general/email');
            $mail = new \Zend\Mail('utf-8');
            $mail->setFrom($email);
            $mail->addTo($email);
            $mail->setSubject('New Unirgy extension installation: ' . $modName);
            $mail->setBodyText(<<<EOT
Hello,

There has been a new installation of Unirgy extension {$modName} on {$_SERVER['HTTP_HOST']}.

Please make sure that this installation was authorized.

Best regards,
Unirgy Installer.
EOT
            );
            $mail->send();
            */
//            }
        }
    }

    public function checkUpdates()
    {
        set_time_limit(0);
        $dbModules = $this->_modelModule->getCollection();

        $uriMods = [];
        /** @var Module $mod */
        foreach ($dbModules as $mod) {
            $modName = $mod->getModuleName();
            if (!$modName) {
                continue;
            }
            $usimpleup = $this->scopeConfig->getValue("{$modName}/usimpleup", 'default');
            if (!$usimpleup || !isset($usimpleup['remote'])) {
                continue;
            }
            $uriMods[(string)$usimpleup['remote'] . $mod->getLicenseKey()][$modName] = $mod;
        }

        $uSimpleUpVersion = $this->getModuleList()->getOne('Unirgy_SimpleUp')['setup_version'];
        foreach ($uriMods as $uri => $mods) {
            $uri .= (strpos($uri, '?') !== false ? '&' : '?') . 'm=2&usuv=' . $uSimpleUpVersion;
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $uri,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => 1,
            ]);
            if ((bool)$this->scopeConfig->isSetFlag('usimpleup/general/verify_ssl')) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                #curl_setopt($curl, CURLOPT_CAINFO, dirname( __DIR__ ) . '/etc/gd_bundle-g2-g1.crt');
                curl_setopt($ch, CURLOPT_CAINFO, dirname( __DIR__ ) . '/ssl/cacert.pem');
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
            $response = curl_exec($ch);
            curl_close($ch);
            if ($response === false) {
                throw new Exception(__('Error while downloading file: %s', curl_error($ch)));
            }
            //$response = @file_get_contents($uri);
            if (!$response) {
                throw new Exception(__('Invalid meta uri resource: %s', $uri));
            }
            //$xml = new Element($response);
            $result = [];
            try {
                $json = trim($response);
                if ($json[0] === '{' || $json[0] === '[') {
                    $result = Json::decode($json, Json::TYPE_ARRAY);
                }
            } catch (Exception $e) {
                if ($e->getMessage() === 'Decoding failed: Syntax error') {
                    $result = [];
                } else {
                    //throw $e;
                    $result = [];
                }
            }
            foreach ($mods as $modName => $mod) {
                if (empty($result[$modName])) {
                    continue;
                }
                $node = $result[$modName];
                if (empty($node['version']['latest'])) {
                    continue;
                }
                /** @var Module $mods [$modName] */
                $mod->setLastChecked(self::now())->setRemoteVersion($node['version']['latest'])->save();
            }
        }
    }

    public function cleanCache()
    {
        $this->_eventManager->dispatch('adminhtml_cache_flush_system');
        $this->_cacheManager->clean($this->_cacheManager->getAvailableTypes());
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
            apc_clear_cache('user');
        }
//        $this->setupUpgrade();
        return $this;
    }

    public function setupUpgrade()
    {
        $keepGenerated = false;
        /** @var \Magento\Setup\Model\InstallerFactory $installerFactory */
        $installerFactory = ObjectManager::getInstance()->get('Magento\Setup\Model\InstallerFactory');
        $logger = ObjectManager::getInstance()->create('Magento\Setup\Model\WebLogger', ['logFile' => 'uinstall.log']);
        $installer = $installerFactory->create($logger);
        $installer->updateModulesSequence($keepGenerated);
        $installer->installSchema();
        $installer->installDataFixtures();
    }

    public function installModules($uris)
    {
        set_time_limit(0);
        foreach ($uris as $uri) {
            if (empty($uri)) {
                continue;
            }
            $filePath = $this->download($uri);
            $this->install($uri, $filePath);
        }
        $this->cleanCache();
    }

    public function upgradeModules($modules)
    {
        set_time_limit(0);
        $modules = $this->_modelModule->getCollection()
            ->addFieldToFilter('module_id', ['in' => $modules]);
        /** @var Module $mod */
        foreach ($modules as $mod) {
            $uri = $mod->getDownloadUri();
            $filePath = $this->download($uri);
            $this->install($uri, $filePath);
        }
        $this->cleanCache();
    }

    public static function now($dayOnly = false)
    {
        return date($dayOnly ? 'Y-m-d' : 'Y-m-d H:i:s');
    }

    /**
     * @return \Magento\Framework\Module\ModuleList|mixed
     * @throws \RuntimeException
     */
    public function getModuleList()
    {
        if ($this->modulesList === null) {
            $this->modulesList = ObjectManager::getInstance()->get('Magento\Framework\Module\ModuleListInterface');
        }

        return $this->modulesList;
    }
}
