<?php
class FW_Queue_Block_Adminhtml_Queue_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('queue_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('fw_queue')->__('Queue Item'));
    }
}