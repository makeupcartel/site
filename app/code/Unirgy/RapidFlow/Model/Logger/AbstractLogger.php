<?php

namespace Unirgy\RapidFlow\Model\Logger;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

abstract class AbstractLogger extends DataObject
{
    protected $_column;
    protected $_line;
    protected $_defaultIoModel;

    public function getIo()
    {
        if (!$this->hasData('io')) {
            $this->setIo($this->_defaultIoModel);
        }
        return $this->getData('io');
    }

    public function setIo($io)
    {
        if (is_string($io)) {
            $io = ObjectManager::getInstance()->create($io);
        }
        $this->setData('io', $io);
        return $this;
    }

    public function seek($position, $whence=SEEK_SET)
    {
        $this->getIo()->seek($position, $whence);
        return $this;
    }

    public function reset()
    {
        $this->getIo()->reset();
        return $this;
    }

    public function start($mode)
    {
        return $this;
    }

    public function pendingProfile()
    {
        return $this;
    }

    public function startProfile()
    {
        return $this;
    }

    public function pauseProfile()
    {
        return $this;
    }

    public function resumeProfile()
    {
        return $this;
    }

    public function stopProfile()
    {
        return $this;
    }

    public function finishProfile()
    {
        return $this;
    }

    public function successRow()
    {
        return $this;
    }

    public function skipRow()
    {
        return $this;
    }

    public function success($message)
    {
        return $this;
    }
    public function warning($message)
    {
        return $this;
    }

    public function error($message)
    {
        return $this;
    }

    public function setLine($line)
    {
//        $this->setData('line', $line);
        $this->_line = $line;
        return $this;
    }

    public function setColumn($col)
    {
        $this->_column = $col;
//        $this->setData('column', $col);
        return $this;
    }

    public function getLine()
    {
        return $this->_line;
//        return $this->_getData('line');
    }

    public function getColumn()
    {
        return $this->_column;
//        return $this->_getData('column');
    }

    public function formatAsExcel()
    {

    }

    public function __destruct()
    {
        $this->unsProfile();
    }
}
