<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Ui\DataProvider\Form\Rule;

use Amasty\Reports\Api\Data\RuleInterface;
use Amasty\Reports\Api\RuleRepositoryInterface;
use Amasty\Reports\Model\Rule;
use Amasty\Reports\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 * @package Amasty\Reports\Ui\DataProvider\Form\Rule
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry,
        RuleRepositoryInterface $ruleRepository,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $result = parent::getData();

        /** @var Rule $current */
        $current = $this->coreRegistry->registry(RuleInterface::PERSIST_NAME);
        if ($current && $current->getEntityId()) {
            $data = $current->getData();
            $result[$current->getEntityId()] = $data;
        } else {
            $data = $this->dataPersistor->get(RuleInterface::PERSIST_NAME);
            if (!empty($data)) {
                /** @var Rule $rule */
                $rule = $this->ruleRepository->getNewRule();
                $rule->setData($data);
                $result[$rule->getId()] = $rule->getData();
                $this->dataPersistor->clear(RuleInterface::PERSIST_NAME);
            }
        }

        return $result;
    }
}
