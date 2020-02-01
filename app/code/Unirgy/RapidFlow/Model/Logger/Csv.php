<?php

namespace Unirgy\RapidFlow\Model\Logger;

class Csv extends AbstractLogger
{
    protected $_defaultIoModel = 'Unirgy\RapidFlow\Model\Io\Csv';

    public function start($mode)
    {
        $this->getIo()->open($this->getProfile()->getLogFilename(), $mode);
        $level = $this->getProfile()->getData('options/log/min_level');
        $this->setLevelSuccess($level == 'SUCCESS');
        $this->setLevelWarning($level == 'SUCCESS' || $level == 'WARNING');
        $this->setLevelError($level == 'SUCCESS' || $level == 'WARNING' || $level == 'ERROR');

        if(PHP_SAPI === 'cli'){
            $this->setData('cli_log', true);
        }
        return $this;
    }

    public function stop()
    {
        $this->getIo()->close();
    }

    public function log($rowType, $data)
    {
        $data = (array)$data;
        if (null !== $rowType) {
            array_unshift($data, $rowType);
        }
        $this->getIo()->write($data);
        if($this->getData('cli_log')){
            printf('%s: %s' . PHP_EOL, date('r'), implode("\t", $data));
        }
        return $this;
    }

    public function success($message = '')
    {
        if ($this->getLevelSuccess()) {
            $this->log('SUCCESS', [$this->getLine(), $this->getColumn(), $message]);
        }
        return $this;
    }

    public function warning($message)
    {
        if ($this->getLevelWarning()) {
            $this->log('WARNING', [$this->getLine(), $this->getColumn(), $message]);
        }
        return $this;
    }

    public function notice($message)
    {
        $this->log('NOTICE', [$this->getLine(), $this->getColumn(), $message]);
        return $this;
    }

    public function error($message)
    {
        if ($this->getLevelError()) {
            $this->log('ERROR', [$this->getLine(), $this->getColumn(), $message]);
        }
        return $this;
    }

    public function setIo($io)
    {
        parent::setIo($io);
        $this->getIo()->setBaseDir($this->getProfile()->getLogBaseDir());
        return $this;
    }
}
