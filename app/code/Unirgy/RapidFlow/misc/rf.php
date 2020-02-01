<?php
/**
 * Created by pp
 * @project magento202
 */
use Magento\Framework\ObjectManagerInterface;

include __DIR__ . '/bootstrap.php';

// Update current locale
///** @var Magento\Framework\Locale\ResolverInterface $localeResolver */
//$localeResolver = $om->get('Magento\Framework\Locale\ResolverInterface');
//$locale = 'nl_NL';
//$oldLocale = $localeResolver->getLocaleFormat();
//$localeResolver->setLocale($locale);
//$localeResolver->setLocale($oldLocale);

function testRfEavExport(ObjectManagerInterface $om)
{
    runRfProfile($om, 'eav-export');
}

function testRfCatExport(ObjectManagerInterface $om)
{
    runRfProfile($om, 'categories-export');
}

function testRfExtraExport(ObjectManagerInterface $om)
{
    runRfProfile($om, 'extras-export');
}

/**
 * Function to
 * @param ObjectManagerInterface $om
 * @param string|int $profile
 * @throws Exception
 */
function runRfProfile(ObjectManagerInterface $om, $profile)
{
    /** @var \Unirgy\RapidFlow\Helper\Data $helper */
    $helper = $om->get('\Unirgy\RapidFlow\Helper\Data');
    $helper->run($profile);
}

//runRfProfile($om, 5);
//testRfEavExport($om);
//
//testRfCatExport($om);
//
//testRfExtraExport($om);

// _ -u _www /usr/bin/php -d memory_limit=512M "<Magento root path>/app/code/Unirgy/RapidFlow/misc/rf.php"
