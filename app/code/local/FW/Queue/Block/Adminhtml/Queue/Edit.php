<?php

class FW_Queue_Block_Adminhtml_Queue_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _construct()
    {
        $this->_objectId = 'queue_id';
        $this->_blockGroup = 'fw_queue';
        $this->_controller = 'adminhtml_queue';
        $this->_headerText = $this->helper('fw_queue')->__('Edit Queue Item');

        parent::_construct();
    }
}
