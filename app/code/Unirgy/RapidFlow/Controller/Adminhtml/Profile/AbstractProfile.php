<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;
if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Helper\Data as HelperData;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Db\Adapter\Pdo\Mysql;
use Psr\Log\LoggerInterface;
use Unirgy\RapidFlow\Helper\Data;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\Profile as ProfileResource;

/**
 * Class AbstractProfile
 * @method Http getResponse()
 * @method \Magento\Framework\App\Request\Http getRequest()
 * @package Unirgy\RapidFlow\Controller\Adminhtml\Profile
 */
abstract class AbstractProfile extends Action
{
    /**
     * @var Session
     */
    protected $_modelAuthSession;

    /**
     * @var Profile
     */
    protected $_profile;


    /**
     * @var HelperData
     */
    protected $_catalogHelperData;

    /**
     * @var Resource
     */
    protected $_profileResource;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    public function __construct(Context $context,
                                Profile $profile,
                                HelperData $catalogHelper,
                                ProfileResource $resource
    )
    {
        $this->_profile = $profile;
        $this->_catalogHelperData = $catalogHelper;
        $this->_profileResource = $resource;
        $this->_logger = Data::logger();

        parent::__construct($context);
    }

    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Unirgy_RapidFlow::urapidflow');
        $resultPage->addBreadcrumb(__('RapidFlow Profile Manager'), __('RapidFlow Profile Manager'));
        $resultPage->getConfig()->getTitle()->prepend(__('RapidFlow'));

//        $this->_setActiveMenu('Unirgy_RapidFlow::urapidflow');
//        $this->_addBreadcrumb(__('RapidFlow Profile Manager'), __('RapidFlow Profile Manager'));
        $resultPage->addContent($resultPage->getLayout()->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile'));
        return $resultPage;
    }

    protected function _validateSecretKey()
    {
        return true;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Unirgy_RapidFlow::urapidflow');
    }

    protected function _getProfile($idField = 'id')
    {
        $profile = $this->_profile;
        $id = $this->getRequest()->getParam($idField);

        if ($id) {
            $profile->load($id);
        }
        if (!$profile->getId()) {
            $this->messageManager->addError(__('Invalid Profile ID'));
        }
        $profile = $profile->factory();

        return $profile;
    }

    protected function _sendUploadResponse($filename, $content, $contentType = 'application/octet-stream')
    {
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setStatusHeader(200, '1.1', 'OK');
        $result->setHeader('Pragma', 'public', true);
        $result->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $result->setHeader('Content-Disposition', 'attachment; filename=' . $filename);
        $result->setHeader('Last-Modified', date('r'));
        $result->setHeader('Accept-Ranges', 'bytes');
        $result->setHeader('Content-Length', strlen($content));
        $result->setHeader('Content-type', $contentType);
        $result->setContents($content);
        /** @var Http $response */
        $response = $this->getResponse();
        $result->renderResult($response);
        $response->sendResponse();
        exit;
    }

    protected function _pipeFile($filePath, $filename, $contentType = 'application/octet-stream')
    {
        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException('404: No such file or directory', 404);
//            header('HTTP/1.1 404 Not Found');
//            echo '<h1>Not found</h1>';
//            exit;
        }

        header('HTTP/1.1 200 OK');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0', true);
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Last-Modified: ' . date('r'));
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . filesize($filePath));
        header('Content-Type: ', $contentType);

        $from = fopen($filePath, 'rb');
        $to = fopen('php://output', 'wb');

        stream_copy_to_stream($from, $to);
        exit;
    }

    protected function _checkIssues()
    {
        $issue1 = $this->_checkEavAttributeIssue();
        $warn = __('This will modify your database, are you sure?');
        if ($issue1) {
            // add warning message with link to fix
            $this->messageManager->addWarning(__("Core Eav Model has potential bug. Click <a href='%1' onclick='return confirm(\"%2\")'>here</a>, to  fix it.",
                                                 $this->getUrl("*/*/fixissues", ['id' => 1]), $warn));
        }

        $issue2 = $this->_checkWebsitePriceInGlobalScope();
        if ($issue2) {
            // add warning with link to fix
            $this->messageManager->addWarning(__("You have website scope prices and global scope config, this is a bug which can prevent RapidFlow from correctly importing prices.
                    Click <a href='%1' onclick='return confirm(\"%2\")'>here</a>, to  fix it.",
                                                 $this->getUrl("*/*/fixissues", ['id' => 2]), $warn));
        }
    }

    /**
     * Checks for pre 1.4.1 magento bug
     * issues with Config::_createAttribute():641 until it's fixed in Magento core
     * @return bool
     */
    protected function _checkEavAttributeIssue()
    {
        try {
            $reflMethod = new \ReflectionMethod('Magento\Eav\Model\Config', '_createAttribute');
            $filename = $reflMethod->getFileName();
            $start_line = $reflMethod->getStartLine() - 1; // it's actually - 1, otherwise you wont get the function() block
            $end_line = $reflMethod->getEndLine();
            $length = $end_line - $start_line;

            $source = file($filename);
            $body = implode("", array_slice($source, $start_line, $length));
            $oldCodeFound = stripos($body, 'isset($attributeData[\'attribute_model\']');
            return $oldCodeFound !== false;
        } catch (\ReflectionException $e) {
            // method not found
            return false;
        }
    }

    /**
     * Checks if there are website level prices when global price scope is configured
     *
     * @return bool
     * @throws \Exception
     */
    protected function _checkWebsitePriceInGlobalScope()
    {
        $isGlobal = $this->_catalogHelperData->isPriceGlobal();
        if ($isGlobal) {
            /** @var ProfileResource $resource */
            $resource = $this->_profileResource;
            /** @var Mysql $conn */
            $conn = $resource->getConnection();
            $delAttrIdsSel = $conn->select()
                ->from(['a' => $resource->getTable('eav_attribute')], ['attribute_id'])
                ->join(['e' => $resource->getTable('eav_entity_type')],
                       'e.entity_type_id=a.entity_type_id', [])
                ->where("e.entity_type_code='catalog_product'")
                ->where("a.backend_model='catalog/product_attribute_backend_price'");

            $sql = sprintf('SELECT count(*) FROM %s WHERE store_id!=0 AND attribute_id IN (%s)',
                           $resource->getTable('catalog_product_entity') . '_decimal', $delAttrIdsSel);
            try {
                $stmt = $conn->query($sql);
                $result = $stmt->fetchAll();

                return !empty($result) && !empty($result[0][0]) && $result[0][0] != 0;
            } catch (\Exception $e) {
                $this->_logger->debug($e->getMessage());
            }

        }

        return false;
    }
}
