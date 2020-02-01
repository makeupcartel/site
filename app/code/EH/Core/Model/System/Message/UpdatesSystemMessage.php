<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2018 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Model\System\Message;

use Magento\Framework\Notification\MessageInterface;
use EH\Core\Helper\Data as CoreHelper;

/**
* Class UpdatesSystemMessage
*/
class UpdatesSystemMessage implements MessageInterface
{
	/**
	 * @var CoreHelper
	 */
	protected $coreHelper;
	
	/**
	 * @var $updateAvailable
	 */
	protected $updateAvailable;
	
	/**
	 * @var $extensionDetails
	 */
	protected $extensionDetails = [];

	/**
	 * @param CoreHelper $coreHelper
	 */
	public function __construct(
		CoreHelper $coreHelper
	) {
		$this->coreHelper = $coreHelper;
	}

	/**
	* Message identity
	*/
	const MESSAGE_IDENTITY = "\x65\x78\x74\x65\x6e\x73\x69\x6f\x6e\x68\x75\x74\x5f\x73\x79\x73\x74\x65\x6d\x5f\x6d\x65\x73\x73\x61\x67\x65";

	/**
	* Retrieve unique system message identity
	*
	* @return string
	*/
	public function getIdentity()
	{
	   return self::MESSAGE_IDENTITY;
	}

	/**
	* Retrieve system message severity
	* Possible default system message types:
	* - MessageInterface::SEVERITY_CRITICAL
	* - MessageInterface::SEVERITY_MAJOR
	* - MessageInterface::SEVERITY_MINOR
	* - MessageInterface::SEVERITY_NOTICE
	*
	* @return int
	*/
	public function getSeverity()
	{
	   return self::SEVERITY_MAJOR;
	}

	/**
	* Check whether the system message should be shown
	*
	* @return bool
	*/
	public function isDisplayed()
	{
		return false;
	}

	/**
	* Retrieve system message text
	*
	* @return boolean
	*/
	public function getText()
	{
		return false;
	}
	
	/**
	* Check if extension update needed
	*
	* @return mixed
	*/
	protected function _checkUpdate($extensionName, $extensionLabel)
	{
	   $extensionDetails = $this->coreHelper->getExtensionVersion($extensionName);
	   if(isset($extensionDetails['status']) && isset($extensionDetails['update_needed']) &&
           $extensionDetails['status'] && $extensionDetails['update_needed']) {
	       if(isset($extensionDetails['label'])) $extensionLabel = $extensionDetails["label"];
			$msg = $extensionLabel. ' ' . $extensionDetails['status_message'] .
				' '.
				$extensionDetails['notification_msg'];
			return $msg;
	   }
	   return false;
	}
	
	/**
	* Get current extension details
	*
	* @return array
	*/
	protected function _getExtensionDetails($class)
    {
        if (empty($this->extensionDetails)) {
            $class = get_class($class);
            if ($class) {
                $class = explode('\\', $class);
                if (isset($class[0]) && isset($class[1])) {
                    $this->extensionDetails['name'] = $class[0] . '_' . $class[1];
                    $this->extensionDetails['label'] = $class[1];
                }
            }
        }

        return $this->extensionDetails;
    }
}
