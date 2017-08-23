<?php
class FW_Queue_Block_Adminhtml_Queue_Edit_Tab_Errors
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('fw/queue/edit/tab/errors.phtml');
    }

    public function getErrors()
    {
        return Mage::registry('current_queue')->getErrors();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('fw_queue')->__('Errors');
    }

    public function getSeverityHtml($severity)
    {
        $severity = Mage::getSingleton('adminnotification/inbox')->getSeverities($severity);
        return '<span class="grid-severity-'.$severity.'"><span>'.ucfirst($severity).'</span></span>';
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('fw_queue')->__('Errors');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}