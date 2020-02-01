<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage;

use BitExpert\ForceCustomerLogin\Api\Repository\WhitelistRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Delete
 *
 * @package BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage
 * @codingStandardsIgnoreFile
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var WhitelistRepositoryInterface
     */
    private $whitelistRepository;
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;
    /**
     * @var Context
     */
    private $context;

    /**
     * Save constructor.
     *
     * @param WhitelistRepositoryInterface $whitelistRepository
     * @param Context $context
     */
    public function __construct(
        WhitelistRepositoryInterface $whitelistRepository,
        Context $context
    ) {
        parent::__construct($context);
        $this->whitelistRepository = $whitelistRepository;
        $this->redirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Delete action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $result = $this->redirectFactory->create();
        $result->setPath('ForceCustomerLogin/Manage/index');

        try {
            if (!$this->whitelistRepository->deleteEntry(
                $this->getRequest()->getParam('id', 0)
            )) {
                throw new \RuntimeException(
                    \sprintf(
                        __('Could not delete manage entry with id %s.'),
                        $this->getRequest()->getParam('id', 0)
                    )
                );
            }

            $this->messageManager->addSuccessMessage(
                __('Whitelist entry successfully removed.')
            );

            $result->setHttpResponseCode(200);
        } catch (\Exception $e) {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR);
            $this->messageManager->addErrorMessage(
                \sprintf(
                    __('Could not remove record: %s'),
                    $e->getMessage()
                )
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BitExpert_ForceCustomerLogin::bitexpert_force_customer_login_manage');
    }
}
