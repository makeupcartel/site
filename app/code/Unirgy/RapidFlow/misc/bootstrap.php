<?php
// REMOVE THIS LINE BEFORE ACTUAL USAGE (added for additional security)
exit(1);

// initialize Magento environment

try {
//    if you move this file, adjust bootstrap.php path
    require __DIR__ . '/../../../../../app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
{$e->getMessage()}
</div>
HTML;
    exit(1);
}

$params = $_SERVER;
$params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'admin'; // change this to appropriate store if needed.
$params[\Magento\Store\Model\Store::CUSTOM_ENTRY_POINT_PARAM] = true;
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params); // bootstrap

/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('Magento\Framework\App\Http');

// configure environment
$om = $bootstrap->getObjectManager();
$areaList = $om->get('Magento\Framework\App\AreaList');
$areaCode = $areaList->getCodeByFrontName('admin');
/** @var \Magento\Framework\App\State $state */
$state = $om->get('Magento\Framework\App\State');
$state->setAreaCode($areaCode);
/** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader */
$configLoader = $om->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
$om->configure($configLoader->load($areaCode));
// end initialize Magento environment

