<?php
// load Magento libraries
include __DIR__ . '/bootstrap.php';

// open original file for reading
$fs = @fopen('var/urapidflow/import/source_file.csv', 'r');
if(!$fs){
    echo 'Source file not found', PHP_EOL;
    return;
}

// open file to be imported for writing
$fd = @fopen('var/urapidflow/import/products.csv', 'w');
if (!$fd) {
    echo 'Destination file not found', PHP_EOL;
    return;
}

// retrieve column names
$fieldColumns = fgetcsv($fs);
$first = true;

// iterate through file
while ($r = fgetcsv($fs)) {
    // get a row as associated array
    $row = array_combine($fieldColumns, $r);

    // perform your data modifications here
    // change existing columns
    $row['price'] *= 1.2;

    // or add new columns,
    // make sure that the new columns are always available
    // and order of columns is always the same
    $row['new_attribute'] = 'Static value';

    // output header
    if ($first) {
        fputcsv($fd, array_keys($row));
        $first = false;
    }

    // write the product row
    fputcsv($fd, $row);
}
// close files
fclose($fd);
fclose($fs);

// run the profile, which should be associated with final import file
$om->get('\Unirgy\RapidFlow\Helper\Data')->run('Your Import Profile');
