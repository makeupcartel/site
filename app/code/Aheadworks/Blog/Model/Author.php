<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Model\ResourceModel\Author as ResourceAuthor;
use Aheadworks\Blog\Model\Author\Validator;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Author
 * @package Aheadworks\Blog\Model
 */
class Author extends AbstractModel implements AuthorInterface, IdentityInterface
{
    /**
     * Blog author cache tag
     */
    const CACHE_TAG = 'aw_blog_author';

    /**
     * Blog listing authors cache tag
     */
    const LISTING_CACHE_TAG = 'aw_blog_authors_listing';

    /**
     * {@inheritdoc}
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Validator $validator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Validator $validator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceAuthor::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->getData(self::FIRSTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstname($name)
    {
        return $this->setData(self::FIRSTNAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->getData(self::LASTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastname($name)
    {
        return $this->setData(self::LASTNAME, $name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getJobPosition()
    {
        return $this->getData(self::JOB_POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setJobPosition($jobPosition)
    {
        return $this->setData(self::JOB_POSITION, $jobPosition);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getImageFile()
    {
        return $this->getData(self::IMAGE_FILE);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setImageFile($file)
    {
        return $this->setData(self::IMAGE_FILE, $file);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getShortBio()
    {
        return $this->getData(self::SHORT_BIO);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setShortBio($shortBio)
    {
        return $this->setData(self::SHORT_BIO, $shortBio);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTwitterId()
    {
        return $this->getData(self::TWITTER_ID);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setTwitterId($twitterId)
    {
        return $this->setData(self::TWITTER_ID, $twitterId);
    }

    /**
     * {@inheritdoc}
     */
    public function getFacebookId()
    {
        return $this->getData(self::FACEBOOK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFacebookId($facebookId)
    {
        return $this->setData(self::FACEBOOK_ID, $facebookId);
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkedinId()
    {
        return $this->getData(self::LINKEDIN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setLinkedinId($linkedinId)
    {
        return $this->setData(self::LINKEDIN_ID, $linkedinId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostsCount()
    {
        return $this->getData(self::POSTS_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPostsCount($count)
    {
        return $this->setData(self::POSTS_COUNT, $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(\Aheadworks\Blog\Api\Data\AuthorExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [self::LISTING_CACHE_TAG, self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        if (!empty($this->getTwitterId())) {
            $this->setTwitterId($this->insertAtCharacter($this->getTwitterId()));
        }
        return parent::beforeSave();
    }

    /**
     * Insert @ symbol to the string
     *
     * @param string $twitterValue
     * @return string
     */
    private function insertAtCharacter($twitterValue)
    {
        $at_character = '@';
        if ($twitterValue[0] != $at_character) {
            $twitterValue = $at_character . $twitterValue;
        }
        return $twitterValue;
    }
}
