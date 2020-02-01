<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Content;

use Magento\Backend\App\Action;

class Delete extends Action
{
    /**
     * @var \Convert\CustomerContent\Model\ContentFactory
     */
    protected $contentFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \Convert\CustomerContent\Model\ContentFactory $contentFactory
     */
    public function __construct(
        Action\Context $context,
        \Convert\CustomerContent\Model\ContentFactory $contentFactory
    ) {
        parent::__construct($context);
        $this->contentFactory = $contentFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $contentId = (int) $this->getRequest()->getParam('content_id');

        if ($contentId) {
            /** @var $contentModel \Convert\CustomerContent\Model\Content */
            $contentModel = $this->contentFactory->create();
            $contentModel->load($contentId);

            // Check this content exists or not
            if (!$contentModel->getId()) {
                $this->messageManager->addError(__('This content no longer exists.'));
            } else {
                try {
                    // Delete content
                    $contentModel->delete();
                    $this->messageManager->addSuccess(__('The content has been deleted.'));

                    // Redirect to grid page
                    $this->_redirect('*/*/');
                    return;
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/edit', ['id' => $contentModel->getId()]);
                }
            }
        }
    }
}