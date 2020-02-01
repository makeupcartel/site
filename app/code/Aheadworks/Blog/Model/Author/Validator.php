<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Author;

use Aheadworks\Blog\Model\ResourceModel\Validator\UrlKeyIsUnique;
use Magento\Framework\Validator\AbstractValidator;
use Aheadworks\Blog\Model\Author;

/**
 * Class Validator
 * @package Aheadworks\Blog\Model\Author
 */
class Validator extends AbstractValidator
{
    /**
     * @var UrlKeyIsUnique
     */
    private $urlKeyIsUnique;

    /**
     * @param UrlKeyIsUnique $urlKeyIsUnique
     */
    public function __construct(
        UrlKeyIsUnique $urlKeyIsUnique
    ) {
        $this->urlKeyIsUnique = $urlKeyIsUnique;
    }

    /**
     * Validate required author data
     *
     * @param Author $author
     * @return bool
     * @throws \Zend_Validate_Exception
     * @throws \Exception
     */
    public function isValid($author)
    {
        $errors = [];
        $twitterIdValidator = new \Zend_Validate_Regex('/^(\@)[A-Za-z0-9_]{1,15}$/i');
        $facebookIdValidator = new \Zend_Validate_Regex('/[A-Za-z0-9_]{1,100}$/i');
        $linkedinIdValidator = new \Zend_Validate_Regex('/[A-Za-z0-9_]{5,30}$/i');

        if (\Zend_Validate::is($author->getTwitterId(), 'NotEmpty')
            && !$twitterIdValidator->isValid($author->getTwitterId())) {
            $errors[] = __('Twitter ID is incorrect.');
        }
        if (\Zend_Validate::is($author->getFacebookId(), 'NotEmpty')
            && !$facebookIdValidator->isValid($author->getFacebookId())) {
            $errors[] = __('Facebook ID is incorrect.');
        }
        if (\Zend_Validate::is($author->getLinkedinId(), 'NotEmpty')
            && !$linkedinIdValidator->isValid($author->getLinkedinId())) {
            $errors[] = __('LinkedIn ID is incorrect.');
        }
        if (!\Zend_Validate::is($author->getFirstname(), 'NotEmpty')) {
            $errors[] = __('First Name can\'t be empty.');
        }
        if (!\Zend_Validate::is($author->getLastname(), 'NotEmpty')) {
            $errors[] = __('Last Name can\'t be empty.');
        }
        if (!\Zend_Validate::is($author->getUrlKey(), 'NotEmpty')) {
            $errors[] = __('URL-key can\'t be empty.');
        }
        if (!$this->urlKeyIsUnique->validate($author)) {
            $errors[] = __('This URL-Key is already assigned to another post, author or category.');
        }

        $this->_addMessages($errors);

        return empty($errors);
    }
}
