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

use BitExpert\ForceCustomerLogin\Api\Data\WhitelistEntryFactoryInterface;
use BitExpert\ForceCustomerLogin\Api\Repository\WhitelistRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Save
 *
 * @package BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage
 * @codingStandardsIgnoreFile
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var WhitelistEntryFactoryInterface
     */
    private $whitelistEntityFactory;
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
     * @param WhitelistEntryFactoryInterface $whitelistEntityFactory
     * @param WhitelistRepositoryInterface $whitelistRepository
     * @param Context $context
     */
    public function __construct(
        WhitelistEntryFactoryInterface $whitelistEntityFactory,
        WhitelistRepositoryInterface $whitelistRepository,
        Context $context
    ) {
        parent::__construct($context);
        $this->whitelistEntityFactory = $whitelistEntityFactory;
        $this->whitelistRepository = $whitelistRepository;
        $this->redirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Save action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $result = $this->redirectFactory->create();

        try {
            $whitelistEntry = $this->whitelistRepository->createEntry(
                $this->getRequest()->getParam('whitelist_entry_id'),
                $this->getRequest()->getParam('label'),
                $this->getRequest()->getParam('url_rule'),
                $this->getRequest()->getParam('strategy'),
                $this->getRequest()->getParam('store_id', 0)
            );

            if (!$whitelistEntry->getId() ||
                !$whitelistEntry->getEditable()) {
                throw new \RuntimeException(
                    __('Could not persist manage entry.')
                );
            }
            $this->messageManager->addSuccessMessage(
                __('Whitelist entry successfully saved.')
            );

            $result->setHttpResponseCode(200);
            $result->setPath('ForceCustomerLogin/Manage/index');
        } catch (\Exception $e) {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR);
            $this->messageManager->addErrorMessage(
                \sprintf(
                    __('Could not add record: %s'),
                    $e->getMessage()
                )
            );

            $result->setPath(
                'ForceCustomerLogin/Manage/Create',
                [
                    'label' => \base64_encode($this->getRequest()->getParam('label')),
                    'url_rule' => \base64_encode($this->getRequest()->getParam('url_rule')),
                    'strategy' => \base64_encode($this->getRequest()->getParam('strategy')),
                    'store_id' => \base64_encode($this->getRequest()->getParam('store_id', 0))
                ]
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
