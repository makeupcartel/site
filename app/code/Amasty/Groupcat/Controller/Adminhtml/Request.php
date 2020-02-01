<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Controller\Adminhtml;

/**
 *  controller
 */
abstract class Request extends \Magento\Backend\App\Action
{
    const CURRENT_REQUEST_MODEL = 'amasty_groupcat_request_model';
    /**
     * @var \Amasty\Groupcat\Model\RequestRepository
     */
    protected $requestRepository;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Groupcat\Model\RequestRepository $requestRepository,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->requestRepository = $requestRepository;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Groupcat::request';

    /**
     * Initiate action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(self::ADMIN_RESOURCE)
            ->_addBreadcrumb(__('Amasty Groupcat'), __('Requests'));

        return $this;
    }
}
