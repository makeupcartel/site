<?php

namespace Unirgy\RapidFlow\Model\Logger;



class Db extends AbstractLogger
{
    public function start($mode)
    {
        return $this;
    }

    public function pause()
    {
        return $this;
    }

    public function stop()
    {
        return $this;
    }

    public function success($message)
    {
        return $this;
    }

    public function error($message)
    {
        return $this;
    }
}
