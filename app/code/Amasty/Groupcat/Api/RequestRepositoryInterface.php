<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Api;

/**
 * Interface RequestRepositoryInterface
 * @api
 */
interface RequestRepositoryInterface
{
    /**
     * @param \Amasty\Groupcat\Api\Data\RequestInterface $request
     * @return \Amasty\Groupcat\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Groupcat\Api\Data\RequestInterface $request);

    /**
     * @param int $requestId
     * @return \Amasty\Groupcat\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($requestId);

    /**
     * @param \Amasty\Groupcat\Api\Data\RequestInterface $request
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Groupcat\Api\Data\RequestInterface $request);

    /**
     * @param int $requestId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($requestId);
}
