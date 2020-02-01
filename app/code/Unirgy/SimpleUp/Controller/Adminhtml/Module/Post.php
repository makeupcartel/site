<?php

namespace Unirgy\SimpleUp\Controller\Adminhtml\Module;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Unirgy\SimpleUp\Controller\Adminhtml\AbstractModule;
use Unirgy\SimpleUp\Helper\Data as HelperData;

class Post extends AbstractModule
{
    /**
     * @var HelperData
     */
    protected $_simpleUpHelper;

    public function __construct(Context $context,
                                HelperData $simpleUpHelper,
                                PageFactory $pageFactory)
    {
        $this->_simpleUpHelper = $simpleUpHelper;
        parent::__construct($context, $pageFactory);
    }

    public function execute()
    {
        $action = $this->getRequest()->getPost('do');
        switch ($action) {
            case __('Download and Install'):
                $this->_forward('install');
                break;
        }

        if ($this->_simpleUpHelper->getModuleList()->has("Unirgy_SimpleLicense")) {
            switch ($action) {
                case __('Add license key'):
                    $this->_forward('addLicense', 'license', 'usimplelic');
                    break;
            }
        }
    }
}
