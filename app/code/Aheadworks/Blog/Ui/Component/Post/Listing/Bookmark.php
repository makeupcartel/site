<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\Component\Post\Listing;

use Magento\Authorization\Model\UserContextInterface;
use Aheadworks\Blog\Model\Source\Post\Status;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Aheadworks\Blog\Model\Serialize\SerializeInterface;
use Aheadworks\Blog\Model\Serialize\Factory as SerializeFactory;

/**
 * Class Bookmark
 * @package Aheadworks\Blog\Ui\Component\Post\Listing
 */
class Bookmark extends \Magento\Ui\Component\Bookmark
{
    const BLOG_LISTING_NAMESPACE = 'aw_blog_post_listing';

    /**
     * @var BookmarkInterfaceFactory
     */
    protected $bookmarkFactory;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var SerializeInterface
     */
    protected $serializer;

    /**
     * @param ContextInterface $context
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param BookmarkInterfaceFactory $bookmarkFactory
     * @param UserContextInterface $userContext
     * @param SerializeFactory $serializeFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        BookmarkInterfaceFactory $bookmarkFactory,
        UserContextInterface $userContext,
        SerializeFactory $serializeFactory,
        array $components = [],
        array $data = []
    ) {
        $this->bookmarkFactory = $bookmarkFactory;
        $this->userContext = $userContext;
        $this->serializer = $serializeFactory->create();
        parent::__construct($context, $bookmarkRepository, $bookmarkManagement, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getConfiguration();
        if (!isset($config['views'])) {
            $this->addView('default', __('Default View'));
            $this->addView(
                'drafts',
                __('Drafts'),
                [
                    'publish_date' =>       ['visible' => false],
                    'published_comments' => ['visible' => false],
                    'new_comments' =>       ['visible' => false],
                    'updated_at' =>         ['visible' => true],
                    'created_at' =>         ['visible' => true]
                ],
                ['status' => [Status::DRAFT]]
            );
            $this->addView(
                'scheduled',
                __('Scheduled Posts'),
                [
                    'published_comments' => ['visible' => false],
                    'new_comments' =>       ['visible' => false],
                    'updated_at' =>         ['visible' => true],
                    'created_at' =>         ['visible' => true]
                ],
                ['status' => [Status::SCHEDULED]]
            );
            // disabled temporary
            /*$this->addView(
                'new_comments',
                __('New Comments'),
                [
                    'published_comments' => ['visible' => false],
                    'new_comments' =>       ['visible' => true],
                    'updated_at' =>         ['visible' => true],
                    'created_at' =>         ['visible' => true]
                ],
                ['new_comments' => ['from' => 1]]
            );*/
        }
    }

    /**
     * Add view to the current config and save the bookmark to db
     * @param string $index
     * @param string $label
     * @param array $changeColumns columns to change comparing to default view config. Array of
     *        elements $colName => ['sorting' => $sorting, 'visible' => $visible, 'position' => $position]
     * @param array $filters applied filters as $filterName => $filterValue array
     * @return $this
     */
    public function addView($index, $label, $changeColumns = [], $filters = [])
    {
        $config = $this->getConfiguration();

        $viewConf = $this->getDefaultViewConfig();
        $viewConf = array_merge($viewConf, [
            'index'     => $index,
            'label'     => $label,
            'value'     => $label,
            'editable'  => false
        ]);
        foreach ($changeColumns as $column => $columnData) {
            if (isset($columnData['sorting'])) {
                $viewConf['data']['columns'][$column]['sorting'] = $columnData['sorting'];
            }
            if (isset($columnData['visible'])) {
                $viewConf['data']['columns'][$column]['visible'] = $columnData['visible'];
            }
            if (isset($columnData['position'])) {
                $config['data']['positions'][$column] = $columnData['position'];
            }
        }
        foreach ($filters as $filterName => $filterValue) {
            $viewConf['data']['filters']['applied'][$filterName] = $filterValue;
        }

        $this->_saveBookmark($index, $label, $viewConf);

        $config['views'][$index] = $viewConf;
        $this->setData('config', array_replace_recursive($config, $this->getConfiguration()));
        return $this;
    }

    /**
     * Save bookmark to db
     *
     * @param string $index
     * @param string $label
     * @param array $viewConf
     * @return void
     */
    protected function _saveBookmark($index, $label, $viewConf)
    {
        $bookmark = $this->bookmarkFactory->create();
        $config = ['views' => [$index => $viewConf]];
        $bookmark->setUserId($this->userContext->getUserId())
            ->setNamespace(self::BLOG_LISTING_NAMESPACE)
            ->setIdentifier($index)
            ->setTitle($label)
            ->setConfig($this->serializer->serialize($config));
        $this->bookmarkRepository->save($bookmark);
    }

    /**
     * @return mixed
     */
    public function getDefaultViewConfig()
    {
        $config['editable']  = false;
        $config['data']['filters']['applied']['placeholder'] = true;
        $config['data']['columns'] = [
            'title'             => ['sorting' => false, 'visible' => true],
            'status'            => ['sorting' => false, 'visible' => true],
            'publish_date'      => ['sorting' => false, 'visible' => true],
            'published_comments'=> ['sorting' => false, 'visible' => true],
            'new_comments'      => ['sorting' => false, 'visible' => true],
            'categories'        => ['sorting' => false, 'visible' => true],
            'tags'              => ['sorting' => false, 'visible' => true],
            'stores'            => ['sorting' => false, 'visible' => true],
            'author_id'       => ['sorting' => false, 'visible' => true],
            'updated_at'        => ['sorting' => false, 'visible' => false],
            'created_at'        => ['sorting' => 'desc', 'visible' => false]
        ];
        $config['data']['displayMode'] = 'grid';
        $position = 0;
        foreach (array_keys($config['data']['columns']) as $colName) {
            $config['data']['positions'][$colName] = $position;
            $position++;
        }

        $config['data']['paging'] = [
            'options' => [
                20 => ['value' => 20, 'label' => 20],
                30 => ['value' => 30, 'label' => 30],
                50 => ['value' => 50, 'label' => 50],
                100 => ['value' => 30, 'label' => 30],
                200 => ['value' => 30, 'label' => 30]
            ],
            'value' => 20
        ];
        return $config;
    }
}
