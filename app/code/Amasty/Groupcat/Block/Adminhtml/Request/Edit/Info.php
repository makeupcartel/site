<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Block\Adminhtml\Request\Edit;

class Info extends \Magento\Backend\Block\Template
{
    protected $currentRequest;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Groupcat\Model\Source\Status
     */
    private $status;

    /**
     * Info constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Amasty\Groupcat\Model\Source\Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Amasty\Groupcat\Model\Source\Status $status,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $context->getStoreManager();
        $this->status = $status;

        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->currentRequest = $this->coreRegistry->registry(
            \Amasty\Groupcat\Controller\Adminhtml\Request::CURRENT_REQUEST_MODEL
        );
        parent::_construct();
    }

    /**
     * Add buttons on request view page
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Send Email'),
                'class' => 'action-save action-secondary',
                'onclick' => 'this.form.submit();'
            ]
        );
        $this->setChild('submit_button', $button);

        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation("' . $this->getUrl('*/*/index') . '")',
                'class' => 'back'
            ]
        );

        $deleteUrl = $this->getUrl(
            '*/*/delete',
            ['id' => $this->getCurrentRequest()->getRequestId()]
        );
        $this->getToolbar()->addChild(
            'delete_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Delete'),
                'onclick' => 'deleteConfirm("' . $this->escapeJsQuote(
                    __(
                        'You are about to delete this request. '
                        . 'Are you sure you want to do that?'
                    )
                ) . '", "' . $deleteUrl . '")',
                'class' => 'delete'
            ]
        );

        parent::_prepareLayout();

        return $this;
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/send', ['request_id' => $this->getCurrentRequest()->getRequestId()]);
    }

    public function getRequestData()
    {
        /** @var \Amasty\Groupcat\Model\Request $model */
        $model = $this->getCurrentRequest();
        $customerName = $model->getName();
        try {
            $customer = $this->customerRepository->get($model->getEmail());
        } catch (\Exception $ex) {
            $customer = null;
        }
        if ($customer) {
            $customerName = sprintf(
                '<a href="%s">%s</a>',
                $this->getUrl('customer/index/edit', ['id' => $customer->getId()]),
                $customerName
            );
        }

        $productHtml = $model->getProductId();
        try {
            $product = $this->productRepository->getById($model->getProductId());
        } catch (\Exception $ex) {
            $product = null;
        }
        if ($product) {
            $productHtml =  sprintf(
                '<a href="%s">%s</a>',
                $this->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                $product->getName()
            );
        }

        $store = $this->storeManager->getStore($model->getStore())->getName();
        $status = $this->status->getOptionByValue($model->getStatus());

        $result =  [
            ['label' => __('Customer Name'), 'value' => $customerName],
            ['label' => __('Customer Email'), 'value' => $model->getEmail()],
            ['label' => __('Customer Phone'), 'value' => $model->getPhone()],
            ['label' => __('Product'), 'value' => $productHtml],
            ['label' => __('Store'), 'value' => $store],
            ['label' => __('Created'), 'value' =>  $model->getCreatedAt()],
            ['label' => __('Status'), 'value' => $status],
            ['label' => __('Comment'), 'value' => $model->getComment()],
        ];

        $answer = $model->getMessageText();
        if ($answer) {
            $result[] = ['label' => __('Admin Answer Text'), 'value' => $answer];
        }

        return $result;
    }

    /**
     * @return \Amasty\Groupcat\Model\Request
     */
    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }
}
