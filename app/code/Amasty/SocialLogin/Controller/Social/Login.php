<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Controller\Social;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;

class Login extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $messages = [
        'success' => [],
        'error'   => []
    ];

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Amasty\SocialLogin\Model\Social
     */
    private $socialModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Amasty\SocialLogin\Model\Repository\SocialRepository
     */
    private $socialRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\SocialLogin\Controller\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @var \Magento\Customer\Model\AuthenticationInterface
     */
    private $authentication;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        \Amasty\SocialLogin\Model\Social $socialModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Amasty\SocialLogin\Model\Repository\SocialRepository $socialRepository,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        \Amasty\SocialLogin\Controller\ResponseHelper $responseHelper,
        \Magento\Customer\Model\AuthenticationInterface $authentication,
        AccountRedirect $accountRedirect,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->socialModel = $socialModel;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->socialRepository = $socialRepository;
        $this->customerRepository = $customerRepository;
        $this->resultRawFactory = $resultRawFactory;
        $this->logger = $logger;
        $this->jsonEncoder = $jsonEncoder;
        $this->responseHelper = $responseHelper;
        $this->accountManagement = $accountManagement;
        $this->customerUrl = $customerUrl;
        $this->authentication = $authentication;
        $this->checkoutSession = $checkoutSession;
        $this->accountRedirect = $accountRedirect;
    }

    /**
     * @return Login|bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type', null);
        if (!$type) {
            $this->addErrorMessage(__('Sorry. We cannot find social type. Please try again later.'));

            return $this->returnAction();
        }

        try {
            $userProfile = $this->socialModel->getUserProfile($type);
            if (!$userProfile->identifier) {
                $this->addErrorMessage(
                    __('Sorry. We cannot find your email. Please enter email in your %1 profile.', ucfirst($type))
                );

                return $this->returnAction();
            }

            $customer = $this->socialRepository->getCustomerBySocial($userProfile->identifier, $type);
            if ($this->session->isLoggedIn()) {
                if ($customer && $customer->getId() !== $this->session->getCustomerId()) {
                    $this->addErrorMessage(
                        __('Sorry. We cannot connect to this social profile. It is used for another site account.')
                    );

                    return $this->returnAction();
                } else {
                    $customer = $this->session->getCustomer()->getDataModel();
                    $user = $this->createUserData($userProfile, $type);
                    $this->socialRepository->createSocialAccount($user, $customer->getId(), $type);
                }
            }

            if (!$customer) {
                if (!$userProfile->email) {
                    $this->session->setUserProfile($userProfile);
                    $this->addErrorMessage(__('We can`t get customer email from your social account.'));

                    return $this->returnAction();
                }
                $customer = $this->getCustomerProcess($userProfile, $type);
            }

            if ($customer && $this->authenticate($customer)) {
                $this->refresh($customer);
                if ($userProfile && $userProfile->identifier) {
                    $this->session->setAmSocialIdentifier($userProfile->identifier);
                }
                $this->addSuccessMessage(__('You have successfully logged in using your %1 account.', ucfirst($type)));
            }
        } catch (LocalizedException $e) {
            $this->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $message = $e->getMessage();
            if (strpos($message, 'oauth') !== false) {
                $message = __('Please check Callback Url in social app configuration');
            } else {
                $message = __('An unspecified error occurred. Please try again later.');
            }

            $this->addErrorMessage($message);
        }

        return $this->returnAction();
    }

    /**
     * @param $customer
     *
     * @return bool
     */
    private function authenticate($customer)
    {
        $customerId = $customer->getId();
        if ($this->authentication->isLocked($customerId)) {
            $this->addErrorMessage(__('The account is locked.'));
            return false;
        }

        $this->authentication->unlock($customerId);
        $this->_eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return true;
    }

    /**
     * @return Login|bool|string
     */
    private function returnAction()
    {
        $this->updateLastCustomerId();

        $type = count($this->messages['error']) ? 0 : 1;
        $messages = $type ? $this->messages['success'] : $this->messages['error'];
        if ($this->getRequest()->getParam('isAjax')) {
            return $this->setResponseData();
        } else {
            foreach ($messages as $message) {
                if ($type) {
                    $this->messageManager->addSuccessMessage($message);
                } else {
                    $this->messageManager->addErrorMessage($message);
                }
            }
            $this->clearMessages();
            return $this->accountRedirect->getRedirect();
        }
    }

    private function updateLastCustomerId()
    {
        $lastCustomerId = $this->session->getLastCustomerId();
        if (isset($lastCustomerId)
            && $this->session->isLoggedIn()
            && $lastCustomerId != $this->session->getId()
        ) {
            $this->session->unsBeforeAuthUrl()
                ->setLastCustomerId($this->session->getId());
        }
    }

    /**
     * @return $this|string
     */
    private function setResponseData()
    {
        $resultType = count($this->messages['error']) ? 0 : 1;
        $messages = $resultType ? $this->messages['success'] : $this->messages['error'];
        $data = [
            'redirect_data' => $this->responseHelper->getRedirectData(),
            'result'   => $resultType,
            'messages' => $messages
        ];

        $this->clearMessages();

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        return $resultRaw->setContents(
            '<script>window.opener.postMessage(' . $this->jsonEncoder->encode($data) . ', "*");window.close();</script>'
        );
    }

    /**
     * @param $userProfile
     * @param $type
     *
     * @return \Amasty\SocialLogin\Model\Customer|bool|CustomerInterface|\Magento\Customer\Model\Customer
     */
    public function getCustomerProcess($userProfile, $type)
    {
        $user = $this->createUserData($userProfile, $type);

        return $this->getCustomerByUser($user, $type);
    }

    /**
     * @param $userProfile
     * @param $type
     *
     * @return array
     */
    private function createUserData($userProfile, $type)
    {
        $name = explode(' ', $userProfile->displayName ?: __('New User'));
        $user = array_merge([
            'email'      => $userProfile->email ?: $userProfile->identifier . '@' . strtolower($type) . '.com',
            'firstname'  => $userProfile->firstName ?: (array_shift($name) ?: $userProfile->identifier),
            'lastname'   => $userProfile->lastName ?: (array_shift($name) ?: ''),
            'identifier' => $userProfile->identifier,
            'type'       => $type
        ], []);

        return $user;
    }

    /**
     * @param array $user
     * @param $type
     *
     * @return \Amasty\SocialLogin\Model\Customer|bool|CustomerInterface|\Magento\Customer\Model\Customer
     */
    public function getCustomerByUser($user, $type)
    {
        try {

            $customer = $this->getCustomerByEmail(
                $user['email'],
                $this->storeManager->getStore()->getWebsiteId()
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $customer = null;
        }

        if (!$customer) {
            try {
                $model = $this->socialRepository->createCustomer($user);
                $customer = $this->accountManagement->createAccount($model);

                $this->_eventManager->dispatch(
                    'customer_register_success',
                    ['account_controller' => $this, 'customer' => $customer]
                );

                $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
                if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                    $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());

                    $this->addSuccessMessage(
                        __(
                            'You must confirm your account. Please check your email for the confirmation '
                            . 'link or <a href="%1">click here</a> for a new link.',
                            $email
                        )
                    );
                }

                $this->addSuccessMessage(__('We have created new store account using your email address.'));
            } catch (\Magento\Framework\Exception\SecurityViolationException $e) {
                $this->addErrorMessage($e->getMessage());
                return false;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->addErrorMessage(__('An unspecified error occurred during creating new customer.'));
                return false;
            }
        }

        $this->socialRepository->createSocialAccount($user, $customer->getId(), $type);

        return $customer;
    }

    /**
     * @param $customer
     */
    private function refresh($customer)
    {
        if ($customer && $customer->getId()) {
            $this->_eventManager->dispatch('amsociallogin_customer_authenticated');
            $this->session->setCustomerDataAsLoggedIn($customer);
            $this->session->regenerateId();
            $this->checkoutSession->loadCustomerQuote();

            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }
        }
    }

    /**
     * @param $email
     * @param null $websiteId
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerByEmail($email, $websiteId = null)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerRepository->get(
            $email,
            $websiteId ?: $this->storeManager->getWebsite()->getId()
        );

        return $customer;
    }

    /**
     * @param $message
     *
     * @return $this
     */
    private function addErrorMessage($message)
    {
        $this->messages['error'][] = $message;

        return $this;
    }

    /**
     * @param $message
     *
     * @return $this
     */
    private function addSuccessMessage($message)
    {
        $this->messages['success'][] = $message;

        return $this;
    }

    /**
     * @return $this
     */
    private function clearMessages()
    {
        $this->messages = [
            'success' => [],
            'error'   => []
        ];

        return $this;
    }
}
