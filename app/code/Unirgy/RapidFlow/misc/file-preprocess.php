<?php
include __DIR__ . '/bootstrap.php';

// download from http location
$res = @copy('http://remote.server.com/archive.zip', $targetDir . 'archive.zip');
if ($res !== true) {
    echo 'ERROR downloading file';
    exit;
}

// unpack the archive
$zip = new ZipArchive();
$res = $zip->open($targetDir . 'archive.zip');
if ($res !== true) {
    echo 'ERROR unpacking archive';
    exit;
}
$zip->extractTo($targetDir);
$zip->close();

// run the profile, which should be associated with final import file
$om->get('\Unirgy\RapidFlow\Helper\Data')->run('Your Import Profile');
