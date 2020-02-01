<?php

namespace Convert\SkinQuiz\Controller\Quiz;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Convert\SkinQuiz\Model\Customerquiz;
use Convert\SkinQuiz\Model\QuizFactory;

class Post extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Context
     */
    private $context;
    protected $customerSession;
    protected $attributeRepository;
    protected $_customerquiz;
    protected $jsonHelper;
    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param MailInterface $mail
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Context $context,
        Customerquiz $customerquiz,
        QuizFactory $quizFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->attributeRepository = $attributeRepository;
        $this->context = $context;
        $this->_customerquiz = $customerquiz;
        $this->_quizFactory = $quizFactory;
        $this->jsonHelper = $jsonHelper;

    }

    /**
     * Post user question
     *
     * @return Redirect
     */

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {

            $params = $this->validatedParams();
            $this->saveGridQuiz($params, $params['emailquiz'], $params['customer-guest']);
            unset($params['fullname']);
            unset($params['emailquiz']);
            unset($params['customer-guest']);
            unset($params['form_key']);
            $id = $this->customerSession->getCustomer()->getId();
            if ($id) 
            {
                $this->_customerquiz->setAtribuiteQuizCustomer($id, $params['age-range'], $params['skin-sensitivity'], $params['skin-type'], $params['skin-concern']);
            }
            $getParams = http_build_query($params);
            $redirectLink = 'skinquiz/quiz/result?' . $getParams;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while processing your form. Please try again later.'));
        }
        return $this->resultRedirectFactory->create()->setPath($redirectLink);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function validatedParams()
    {
        $request = $this->getRequest();

        if (trim($request->getParam('emailquiz')) === '') {
            throw new LocalizedException(__('Enter the Email and try again.'));
        }

        return $request->getParams();
    }

    public function saveGridQuiz($quizgues, $email, $userquiz)
    {
        $jsonquiz = $this->encode($quizgues);
        $quizmodel = $this->_quizFactory->create();
        $quizmodel->setGuestquiz($jsonquiz);
        $quizmodel->setUserquiz($userquiz);
        $quizmodel->setEmail($email);
        return $quizmodel->save();
    }

    public function encode(array $data)
    {
        $encodeData = $this->jsonHelper->jsonEncode($data);
        return $encodeData;
    }
}
