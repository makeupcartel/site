<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2019 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_StaticBlockByCustomerGroup
 */

namespace EH\StaticBlockByCustomerGroup\Model\System\Message;

use EH\Core\Model\System\Message\UpdatesSystemMessage as CoreUpdatesSystemMessage;

/**
* Class UpdatesSystemMessage
*/
class UpdatesSystemMessage extends CoreUpdatesSystemMessage
{

	/**
	* Check whether the system message should be shown
	*
	* @return bool
	*/
	public function isDisplayed()
	{
		if (!$this->updateAvailable) {
			$extensionDetails = $this->_getExtensionDetails($this);
			if ($extensionDetails['name'] && $extensionDetails['label']) {
				$this->updateAvailable = $this->_checkUpdate($extensionDetails['name'], $extensionDetails['label']);	
			}
		}
		if ($this->updateAvailable) {
			return true;
		}
		return false;
	}

	/**
	* Retrieve system message text
	*
	* @return \Magento\Framework\Phrase
	*/
	public function getText()
	{
		if (!$this->updateAvailable) {
			$extensionDetails = $this->_getExtensionDetails($this);
			if ($extensionDetails['name'] && $extensionDetails['label']) {
				$this->updateAvailable = $this->_checkUpdate($extensionDetails['name'], $extensionDetails['label']);	
			}
		}
		if ($this->updateAvailable) {
			return __($this->updateAvailable);
		}
	}
}
