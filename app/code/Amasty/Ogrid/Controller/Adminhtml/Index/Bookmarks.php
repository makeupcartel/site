<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Controller\Adminhtml\Index;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Controller\Adminhtml\AbstractAction;

class Bookmarks  extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context,
//        UiComponentFactory $factory,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        BookmarkInterfaceFactory $bookmarkFactory,
        UserContextInterface $userContext,
        DecoderInterface $jsonDecoder,
        EncoderInterface $jsonEncoder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Element\UiComponentFactory $factory
    ) {
        parent::__construct($context);
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkFactory = $bookmarkFactory;
        $this->userContext = $userContext;
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->factory = $factory;
    }

    public function execute()
    {
        $bookmark = $this->bookmarkFactory->create();
        $jsonData = $this->_request->getParam('data');

        if (!$jsonData) {
            throw new \InvalidArgumentException('Invalid parameter "data"');
        }
        $data = $this->jsonDecoder->decode($jsonData);

        $action = key($data);

        $this->updateBookmark(
            $bookmark,
            $action,
            $bookmark->getTitle(),
            $jsonData
        );

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'grid' => $this->getGridData()
        ]);
    }

    protected function updateBookmark(BookmarkInterface $bookmark, $identifier, $title, $config)
    {
        $updateBookmark = $this->checkBookmark($identifier);
        if ($updateBookmark !== false) {
            $bookmark = $updateBookmark;
        }

        $bookmark->setUserId($this->userContext->getUserId())
            ->setNamespace($this->_request->getParam('namespace'))
            ->setIdentifier($identifier)
            ->setTitle($title)
            ->setConfig($config);
        $this->bookmarkRepository->save($bookmark);
    }

    protected function checkBookmark($identifier)
    {
        $result = false;

        $updateBookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            $identifier,
            $this->_request->getParam('namespace')
        );

        if ($updateBookmark) {
            $result = $updateBookmark;
        }

        return $result;
    }

    protected function getGridData()
    {
        $component = $this->factory->create($this->_request->getParam('namespace'));

        $this->prepareComponent($component);
        $grid = \Zend_Json::decode($component->render());

        return $grid;
    }

    protected function prepareComponent(\Magento\Framework\View\Element\UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_order');
    }
}