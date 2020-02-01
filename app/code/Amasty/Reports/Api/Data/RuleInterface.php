<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Api\Data;

interface RuleInterface
{
    const TABLE_NAME = 'amasty_reports_rule';

    const PERSIST_NAME = 'amasty_report_rule';

    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';

    const TITLE = 'title';

    const STATUS = 'status';

    const UPDATED_AT = 'updated_at';
    
    const CONDITIONS = 'conditions_serialized';

    const PIN = 'pin';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function setEntityId($entityId);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function setTitle($title);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string|null $updatedAt
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string|null
     */
    public function getSerializedConditions();

    /**
     * @param string|null $conditions
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function setSerializedConditions($conditions);

    /**
     * @return bool
     */
    public function isConditionEmpty();

    /**
     * @return int
     */
    public function getPin();

    /**
     * @param int $pin
     *
     * @return \Amasty\Reports\Api\Data\RuleInterface
     */
    public function setPin($pin);
}
