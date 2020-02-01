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
use BitExpert\ForceCustomerLogin\Model\WhitelistEntry;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class RestoreDefault
 *
 * @package BitExpert\ForceCustomerLogin\Controller\Adminhtml\Manage
 * @codingStandardsIgnoreFile
 */
class RestoreDefault extends \Magento\Framework\App\Action\Action
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
     * @var array Default routes
     */
    private $defaultRoutes;

    /**
     * Save constructor.
     *
     * @param WhitelistEntryFactoryInterface $whitelistEntityFactory
     * @param WhitelistRepositoryInterface $whitelistRepository
     * @param Context $context
     * @param array $defaultRoutes
     */
    public function __construct(
        WhitelistEntryFactoryInterface $whitelistEntityFactory,
        WhitelistRepositoryInterface $whitelistRepository,
        Context $context,
        array $defaultRoutes
    ) {
        parent::__construct($context);
        $this->whitelistEntityFactory = $whitelistEntityFactory;
        $this->whitelistRepository = $whitelistRepository;
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->context = $context;
        $this->defaultRoutes = $defaultRoutes;
    }

    /**
     * Restore whitelist defaults action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $result = $this->redirectFactory->create();
        $result->setPath('ForceCustomerLogin/Manage/index');

        $whiteLists = $this->getWhiteListEntriesIndexedByPath();

        try {
            foreach ($this->defaultRoutes as $route => $description) {
                if (\array_key_exists($route, $whiteLists)) {
                    continue;
                }

                $this->whitelistRepository->createEntry(null, $description, $route);
            }
        } catch (\Exception $e) {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR);
            $this->messageManager->addErrorMessage(
                __("Could not restore default whitelist!")
            );
            return $result;
        }

        $result->setHttpResponseCode(200);
        $this->messageManager->addSuccessMessage(
            __("Successfully restored whitelist defaults.")
        );

        return $result;
    }

    /**
     * Get all current whitelists indexed by it's url rule
     *
     * @return array
     */
    protected function getWhiteListEntriesIndexedByPath()
    {
        $whiteListCollection = $this->whitelistRepository->getCollection();
        $whiteLists = [];

        foreach ($whiteListCollection->getItems() as $whiteList) {
            $whiteLists[$whiteList->getData(WhitelistEntry::KEY_URL_RULE)] =
                $whiteList->getData(WhitelistEntry::KEY_LABEL);
        }

        return $whiteLists;
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
