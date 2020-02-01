<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Rss\Post;

/**
 * Interface RssItemInterface
 *
 * @package Aheadworks\Blog\Model\Rss\Post
 */
interface RssItemInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const TITLE = 'title';
    const LINK = 'link';
    const DESCRIPTION = 'description';
    const DATE_CREATED = 'dateCreated';
    /**#@-*/

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get link
     *
     * @return string
     */
    public function getLink();

    /**
     * Set link
     *
     * @param string $link
     * @return $this
     */
    public function setLink($link);

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get publication date
     *
     * @return int
     */
    public function getDateCreated();

    /**
     * Set publication date
     *
     * @param int $dateCreated
     * @return $this
     */
    public function setDateCreated($dateCreated);
}
