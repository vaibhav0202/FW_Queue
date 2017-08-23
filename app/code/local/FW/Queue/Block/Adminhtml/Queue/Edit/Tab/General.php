<?php
class FW_Queue_Block_Adminhtml_Queue_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{


        /**
         * Prepare the inn form wrapper
         * @return \Mage_Adminhtml_Block_Widget_Form
         */
    protected function _prepareForm()
    {
        $queue = Mage::registry('current_queue');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('queue_form', array('legend' => $this->__('Manual Edit of Queue Item')));

        $fieldset->addField('queue_id', 'hidden', array('name' => 'queue_id'));

        $fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => 'State of Queue Item',
            'options' => Mage::getModel('fw_queue/queue')->getStatusesOptions()
        ));

        //Comment out for now, when the form is saved there is no code to actually take any action
       /* $fieldset->addField('action', 'select', array(
            'name' => 'action',
            'label' => $this->__('Action'),
            'options' => array(
                0 => $this->__('Please Select'),
                1 => $this->__('Process'),
                2 => $this->__('Reset'),
                3 => $this->__('Remove'),
            ),
        ));*/

        $fieldset->addField('number_attempts', 'text', array(
            'name' => 'number_attempts',
            'label' => 'Number of attemps made',
        ));

        $fieldset->addField('created_at', 'note', array(
            'label' => $this->__('Created At'),
            'title' => $this->__('Created At'),
            'text'  => $queue->getCreatedAt()
        ));

        $fieldset->addField('last_attempt', 'note', array(
            'label' => $this->__('Last Attempted'),
            'title' => $this->__('Last Attempted'),
            'text'  => $queue->getLastAttempt()
        ));

        $fieldset->addField('short_description', 'textarea', array(
            'name'  => 'short_description',
            'label' => 'Description',
        ));

        $fieldset->addField('queue_item_data', 'textarea', array(
            'name'  => 'queue_item_data',
            'label' => 'Queue Item Data',
            'note'  => '<strong>WARNING: editing this information can cause system instability.</strong>'
        ));

        if ($queue->getQueueId())
        {
            $form->setValues($queue->getData());
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('fw_queue')->__('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('fw_queue')->__('General');
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

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return true;
    }

    /**
     * Retrieve datetime format
     *
     * @return unknown
     */
    protected function _getDateTimeFormat()
    {
        return Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
    }

    protected function renderDateTime($data)
    {
        $format = $this->_getDateTimeFormat();
        try {
            $data = Mage::app()->getLocale()->date($data, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
        }
        catch (Exception $e)
        {
            $data = Mage::app()->getLocale()->date($data, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
        }
        return $data;
    }
}
