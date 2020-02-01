<?php

namespace Convert\BookSkin\Controller\IndexBook;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Contact\Model\ConfigInterface;
use Convert\BookSkin\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

/**
 * Class Post
 *
 * @package Convert\BookSkin\Controller\IndexBook
 */
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

	/**
	 * @var MailInterface
	 */
	private $mail;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param Context $context
	 * @param MailInterface $mail
	 * @param DataPersistorInterface $dataPersistor
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		Context $context,
		MailInterface $mail,
		DataPersistorInterface $dataPersistor,
		LoggerInterface $logger = null
	)
	{
		parent::__construct($context);
		$this->context = $context;
		$this->mail = $mail;
		$this->dataPersistor = $dataPersistor;
		$this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
	}

	/**
	 * Post user book skin request
	 *
	 * @return Redirect
	 */
	public function execute()
	{
		if (!$this->getRequest()->isPost()) {
			return $this->resultRedirectFactory->create()->setPath('*/*/');
		}
		try {
			$this->sendEmail($this->validatedParams());
			$this->messageManager->addSuccessMessage(
				__('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
			);
			$this->dataPersistor->clear('book_skin');
		} catch (LocalizedException $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
			$this->dataPersistor->set('book_skin', $this->getRequest()->getParams());
		} catch (\Exception $e) {
			$this->logger->critical($e);
			$this->messageManager->addErrorMessage(
				__('An error occurred while processing your form. Please try again later.')
			);
			$this->dataPersistor->set('book_skin', $this->getRequest()->getParams());
		}
		return $this->resultRedirectFactory->create()->setPath('book-skin');
	}

	/**
	 * @param array $post Post data from contact form
	 * @return void
	 */
	private function sendEmail($post)
	{
		$this->mail->send(
			$post['email'],
			['data' => new DataObject($post)]
		);
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	private function validatedParams()
	{
		$request = $this->getRequest();
		if (trim($request->getParam('name')) === '') {
			throw new LocalizedException(__('Enter the First Name and try again.'));
		}
		if (trim($request->getParam('last_name')) === '') {
			throw new LocalizedException(__('Enter the Last Name and try again.'));
		}
		if (false === \strpos($request->getParam('email'), '@')) {
			throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
		}
		if (trim($request->getParam('hideit')) !== '') {
			throw new \Exception();
		}

		if ($request->getParam('skin_concern') == 'Please select') {
			throw new LocalizedException(__('Please choose Skin Concern.'));
		}

		return $request->getParams();
	}
}
