<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Controller\Adminhtml\Request;

use Amasty\Groupcat\Model\Source\Status;

class Send extends \Amasty\Groupcat\Controller\Adminhtml\Request
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Groupcat\Model\RequestRepository $requestRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Amasty\Groupcat\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context, $requestRepository, $coreRegistry);
        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('request_id');

        $message = $this->getRequest()->getParam('email_text');
        if (!\Zend_Validate::is(trim($message), 'NotEmpty')) {
            $this->messageManager->addErrorMessage(__('Please enter a Email Text.'));
            $this->_redirect('amasty_groupcat/edit/*');
            return;
        }

        if ($id) {
            try {
                $model = $this->requestRepository->get($id);

                $emailTo = $model->getEmail();
                $sender = $this->helper->getModuleConfig('general/sender');
                $template = $this->helper->getModuleConfig('general/template');
                if ($this->sendEmail($model, $sender, $emailTo, $template, $message)) {
                    $model->setStatus(Status::ANSWERED);
                    $model->setMessageText($message);
                    $this->requestRepository->save($model);
                    $this->messageManager->addSuccessMessage(__('Email Answer was sent.'));
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This request no longer exists.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please select request id.'));
        }

        $this->_redirect('amasty_groupcat/*/');
    }

    /**
     * @param \Amasty\Groupcat\Model\Request $model
     * @param array|string $sender
     * @param array|string $emailTo
     * @param string $template
     * @param string $message
     * @return bool
     */
    private function sendEmail(\Amasty\Groupcat\Model\Request $model, $sender, $emailTo, $template, $message)
    {
        try {
            $store = $this->storeManager->getStore($model->getStoreId());
            $data =  [
                'website_name'  => $store->getWebsite()->getName(),
                'group_name'    => $store->getGroup()->getName(),
                'store_name'    => $store->getName(),
                'request'       => $model,
                'message'       => $message,
                'customer_name' => $model->getName()
            ];

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
            )->setTemplateVars(
                $data
            )->setFrom(
                $sender
            )->addTo(
                $emailTo,
                $model->getName()
            )->getTransport();

            $transport->sendMessage();
            return true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        }
    }
}
