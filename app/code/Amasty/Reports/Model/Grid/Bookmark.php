<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\Grid;

use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Model\BookmarkFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class Bookmark
 * @package Amasty\Reports\Model\Grid
 */
class Bookmark
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var BookmarkRepositoryInterface
     */
    private $bookmarkRepository;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var BookmarkInterface
     */
    private $bookmark;

    /**
     * @var BookmarkFactory
     */
    private $bookmarkFactory;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    public function __construct(
        BookmarkManagementInterface $bookmarkManagement,
        BookmarkRepositoryInterface $bookmarkRepository,
        EncoderInterface $encoder,
        UserContextInterface $userContext,
        BookmarkFactory $bookmarkFactory
    ) {
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->encoder = $encoder;
        $this->bookmarkFactory = $bookmarkFactory;
        $this->userContext = $userContext;
    }

    /**
     * @param string $namespace
     *
     * @return Bookmark $this
     */
    public function load($namespace)
    {
        $this->bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            BookmarkInterface::CURRENT,
            $namespace
        );
        if (!$this->bookmark) {
            $this->bookmark = $this->bookmarkFactory->create()
                ->setNamespace($namespace)
                ->setIdentifier(BookmarkInterface::CURRENT)
                ->setConfig([])
                ->setUserId($this->userContext->getUserId());
        }

        return $this;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->bookmark = null;
    }

    /**
     * $filters array like [code => value]
     *
     * @param string $namespace
     * @param array $filters
     */
    public function applyFilter($namespace, $filters)
    {
        if (!$this->bookmark) {
            $this->load($namespace);
        }

        $config = $this->bookmark->getConfig();
        // clear previous filters
        $config[BookmarkInterface::CURRENT]['filters'] = ['applied' => ['placeholder' => true]];

        foreach ($filters as $filterCode => $filterValue) {
            $this->injectFilter($config, $filterCode, $filterValue);
        }
        $this->bookmark->setConfig($this->encoder->encode($config));

        $this->bookmarkRepository->save($this->bookmark);
    }

    /**
     * @param array $config
     * @param string $filterCode
     * @param string $filterValue
     */
    private function injectFilter(&$config, $filterCode, $filterValue)
    {
        if ($filterValue !== null && isset($config[BookmarkInterface::CURRENT]['filters']['applied'])) {
            $config[BookmarkInterface::CURRENT]['filters']['applied'][$filterCode] = $filterValue;
        }
    }
}
