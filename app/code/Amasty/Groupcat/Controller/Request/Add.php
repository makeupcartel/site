<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Controller\Request;

use Amasty\Groupcat\Model\Request;
use Magento\Framework\Exception\LocalizedException;

class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Amasty\Groupcat\Model\RequestFactory
     */
    private $requestFactory;

    /**
     * @var \Amasty\Groupcat\Model\RequestRepository
     */
    private $requestRepository;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Data\Form\Filter\Escapehtml
     */
    private $escapehtml;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Amasty\Groupcat\Model\RequestFactory $requestFactory
     * @param \Amasty\Groupcat\Model\RequestRepository $requestRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Amasty\Groupcat\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Form\Filter\Escapehtml $escapehtml
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Amasty\Groupcat\Model\RequestFactory $requestFactory,
        \Amasty\Groupcat\Model\RequestRepository $requestRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Amasty\Groupcat\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Form\Filter\Escapehtml $escapehtml
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->requestFactory = $requestFactory;
        $this->requestRepository = $requestRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->escapehtml = $escapehtml;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $message = [
            'error' => __('Sorry. There is a problem with Your Quote Request.' .
                ' Please try again or use Contact Us link in the menu.'
            )
        ];
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getValidData();

                /** @var  Request $model */
                $model = $this->requestFactory->create();
                $model->addData($data);
                $this->requestRepository->save($model);
                $message = ['success' => __('Thanks for contacting us. We\'ll respond to you as soon as possible. ')];

                $this->sendAdminNotification($model);
                $this->sendAutoReply($model);

            } catch (LocalizedException $e) {
                $message = ['error' => $e->getMessage()];
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        $this->getResponse()->representJson(
            $this->jsonEncoder->encode($message)
        );
    }

    /**
     * Validates all data
     *
     * @throws LocalizedException
     * @return array
     */
    private function getValidData()
    {
        $params = $this->getRequest()->getPostValue();
        $data = [
            'email' => $params['email'],
            'name' => $params['name'],
            'phone' => $params['phone'],
            'product_id' => $params['product_id']
        ];
        if (isset($params['comment'])) {
            $data['comment'] = $params['comment'];
        }

        if ($this->helper->getModuleStoreConfig('gdpr/enabled')
            && !isset($params['gdpr'])
        ) {
            throw new LocalizedException(
                __('For request sending you have to agree with our privacy policy.')
            );
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(
                __('Form key is not valid. Please try to reload the page.')
            );
        }

        if (!\Zend_Validate::is($data['email'], 'EmailAddress')) {
            throw new LocalizedException(__('Please enter a valid email address.'));
        }

        $data['name'] = $this->escapehtml->outputFilter(trim($data['name']));
        if (!\Zend_Validate::is($data['name'], 'NotEmpty')) {
            throw new LocalizedException(__('Please enter a name.'));
        }

        $data['phone'] = $this->escapehtml->outputFilter(trim($data['phone']));
        if (!\Zend_Validate::is($data['phone'], 'NotEmpty')) {
            throw new LocalizedException(__('Please enter a phone.'));
        }

        $data['product_id'] = (int)$data['product_id'];
        if (!\Zend_Validate::is($data['product_id'], 'NotEmpty')
           || !($product = $this->productRepository->getById($data['product_id']))
        ) {
            throw new LocalizedException(__('There are no product for your request.'));
        } else {
            $data['product_name'] = $product->getName();
        }

        if (array_key_exists('comment', $data)) {
            $data['comment'] = $this->escapehtml->outputFilter(trim($data['comment']));
        }

        $data['store_id'] = $this->storeManager->getStore()->getId();

        return $data;
    }

    /**
     * @param Request $model
     */
    private function sendAdminNotification(Request $model)
    {
        $emailTo = trim($this->helper->getModuleStoreConfig('admin_email/to'));
        if ($emailTo) {
            $sender = $this->helper->getModuleStoreConfig('admin_email/sender');
            $template = $this->helper->getModuleStoreConfig('admin_email/template');
            $this->sendEmail($model, $sender, $emailTo, $template);
        }
    }

    /**
     * @param Request $model
     */
    private function sendAutoReply(Request $model)
    {
        $enabled = $this->helper->getModuleStoreConfig('reply_email/enabled');
        if ($enabled) {
            $emailTo = $model->getEmail();
            $sender = $this->helper->getModuleStoreConfig('reply_email/sender');
            $template = $this->helper->getModuleStoreConfig('reply_email/template');
            $this->sendEmail($model, $sender, $emailTo, $template);
        }
    }

    /**
     * @param Request $model
     * @param array|string $sender
     * @param array|string $emailTo
     * @param string $template
     */
    private function sendEmail(Request $model, $sender, $emailTo, $template)
    {
        try {
            $store = $this->storeManager->getStore();
            $data =  [
                'website_name'  => $store->getWebsite()->getName(),
                'group_name'    => $store->getGroup()->getName(),
                'store_name'    => $store->getName(),
                'request'       => $model,
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
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
