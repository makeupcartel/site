<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Author;

use Aheadworks\Blog\Controller\Adminhtml\Upload;

/**
 * Class UploadImage
 * @package Aheadworks\Blog\Controller\Adminhtml\Author
 */
class UploadImage extends Upload
{
    /**
     * @var string
     */
    const FILE_ID = 'image_file';

    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Blog::authors';
}
