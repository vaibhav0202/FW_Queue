<?php
class FW_Queue_Model_Resource_Queue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
    	$this->_init('fw_queue/queue');
    }
}