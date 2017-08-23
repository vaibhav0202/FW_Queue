<?php
class FW_Queue_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Whether Queue is enabled
	 * @return bool
	 */
	public function isQueueEnabled()
	{
            return Mage::getStoreConfig('queue_setting/queue_enable/active');
	}
}