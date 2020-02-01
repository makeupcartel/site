<?php

namespace Unirgy\RapidFlow\Helper;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonologLogger;

class Logger extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/rf.log';

    /**
     * @var int
     */
    protected $loggerType = MonologLogger::DEBUG;

    public function setFile($file)
    {
        $this->fileName = $file;
    }
}
