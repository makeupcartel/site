<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


// phpcs:ignoreFile

namespace Amasty\Reports\Model\Grid\Export\Sales\Overview;

use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToCsv as OriginalConvertToCsv;

/**
 * Class ConvertToCsv
 */
class ConvertToCsv extends OriginalConvertToCsv
{
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        $pageSize = 200
    ) {
        parent::__construct($filesystem, $filter, $metadataProvider, $pageSize);
    }
}
