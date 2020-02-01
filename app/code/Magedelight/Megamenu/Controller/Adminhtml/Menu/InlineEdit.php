<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class InlineEdit
 *
 * @package Magedelight\Megamenu\Controller\Adminhtml\Menu
 */
class InlineEdit extends \Magento\Backend\App\Action
{

    /** @var PageFactory  */
    protected $pageFactory;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @var \Magedelight\Megamenu\Model\Menu
     */
    protected $menuModel;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        \Magedelight\Megamenu\Model\Menu $menuModel
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->jsonFactory = $jsonFactory;
        $this->menuModel = $menuModel;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                        'messages' => [__('Please correct the data sent.')],
                        'error' => true,
            ]);
        }

        $model = $this->menuModel;

        $this->_eventManager->dispatch(
            'megamenu_menu_prepare_inlinesave',
            ['menu' => $model, 'request' => $this->getRequest()]
        );


        foreach (array_keys($postItems) as $menuId) {
            try {
                if ($postItems[$menuId]['menu_id']) {
                    $model->load($menuId);
                    $model->setData($postItems[$menuId]);
                    $model->save();
                    $messages[] = __('Menu saved.');
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithMenuId($menuId, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithMenuId($menuId, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithMenuId(
                    $menuId,
                    __('Something went wrong while saving the menu.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
                    'messages' => $messages,
                    'error' => $error
        ]);
    }

    /**
     * Add menu title to error message
     *
     * @param string $menuId
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithMenuId($menuId, $errorText)
    {
        return '[Menu ID: ' . $menuId . '] ' . $errorText;
    }
}
