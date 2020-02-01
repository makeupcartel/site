<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */

namespace Amasty\Oaction\Model;

use Magento\Framework\App\ResourceConnection;

class Command
{
    protected $_type = '';

    protected $_info = [];

    protected $_errors = [];

    public function __construct()
    {
    }

    public function getCreationData()
    {
        if (isset($this->_info)) {
            return $this->_info;
        } else {
            return false;
        }
    }

    /**
     * Gets list of not critical errors after the command execution
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    public function hasResponse()
    {
        return false;
    }

    public function getResponseName()
    {
        return '';
    }

    public function getResponseType()
    {
        return 'application/pdf';
    }

    public function getResponseBody()
    {
        return 'application/pdf';
    }
}
