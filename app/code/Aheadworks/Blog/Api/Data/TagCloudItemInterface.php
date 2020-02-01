<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Api\Data;

/**
 * Tag Cloud Item interface
 * @api
 */
interface TagCloudItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const TAG = 'tag';
    const POST_COUNT = 'post_count';
    /**#@-*/

    /**
     * Get Tag
     *
     * @return \Aheadworks\Blog\Api\Data\TagInterface
     */
    public function getTag();

    /**
     * Set Tag
     *
     * @param \Aheadworks\Blog\Api\Data\TagInterface $tag
     * @return $this
     */
    public function setTag($tag);

    /**
     * Get number of posts
     *
     * @return int
     */
    public function getPostCount();

    /**
     * Get number of posts
     *
     * @param int $postCount
     * @return $this
     */
    public function setPostCount($postCount);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Blog\Api\Data\TagCloudItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Blog\Api\Data\TagCloudItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(TagCloudItemExtensionInterface $extensionAttributes);
}
