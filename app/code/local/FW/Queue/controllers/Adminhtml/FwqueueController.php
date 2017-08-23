<?php

class FW_Queue_Adminhtml_FwqueueController extends Mage_Adminhtml_Controller_Action
{
    
    public function indexAction()
    {
        $this->_redirect('*/*/list');
    }
    
    /**
     * Display grid
     */
    public function listAction()
    {
        $this->_getSession()->setFormData(array());
        $this->_title($this->__('Queue List'));
        $this->loadLayout();
        $this->_setActiveMenu('queue');
        
        $this->renderLayout();
    }
    
    /**
     * Check ACL permissions
     * @return
     */
    public function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('fw_queue/queue');
    }
    
    /**
     * Grid action for ajax request
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }
    
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    public function editAction()
    { 
        $model = Mage::getModel('fw_queue/queue');
        Mage::register('current_queue', $model);
        $id = $this->getRequest()->getParam('queue_id');

        try{
            if($id){
                if(!$model->load($id)->getQueueId()){
                    Mage::throwException($this->__('No record found with id "%s"', $id));
                }
            }

            $this->loadLayout();
            $this->renderLayout();
        }catch(Exception $e){
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/list');
        }
    }
    
    /**
     * Process a form
     */
    public function saveAction()
    {
        if($data = $this->getRequest()->getPost()) {
            $this->_getSession()->setFormData($data);
            $model = Mage::getModel('fw_queue/queue');
            $id = $this->getRequest()->getParam('queue_id');
            
            try{
                if($id){
                    $model->load($id);
                }

                $model->addData($data);
                $model->save();
                
                $this->_getSession()->addSuccess(
                    $this->__('Queue was successfully saved.')
                );
                $this->_getSession()->setFormData(false);
                
                if($this->getRequest()->getParam('back'))
                {
                    $params = array('queue_id' => $model->getQueueId());
                    $this->_redirect( '*/*/edit', $params);
                }else{
                    $this->_redirect('*/*/list');
                }
            }catch(Exception $e){
                $this->_getSession()->addError($e->getMessage());
                if($model && $model->getQueueId())
                {
                    $this->_redirect('*/*/edit', array('queue_id' => $model->getQueueId()));
                }else{
                    $this->_redirect('*/*/new');
                }
            }
            return;
        }
        
        $this->_getSession()->addError($this->__('No data found to save'));
        $this->_redirect('*/*/list');
    }
    
   
    
    public function processAction()
    {
        $f = new Zend_Filter_Int();
        $id = $f->filter($this->getRequest()->getParam('queue_id'));
        if(!$id)
        {
            $this->_getSession()->addError('No id sent to find queue to process.');
            $this->_redirect('*/*/list');
        }
        $queue = Mage::getModel('fw_queue/queue');
        $queue->load($id);
        $queue->process();

        $this->_redirect('*/*/list');
    }
    
    public function resetAction()
    {
        $id =$this->getRequest()->getParam('queue_id');
        if(!$id)
        {
            $this->getSession()->addError('No id sent to find queue to process.');
            $this->_redirect('*/*/list');
        }
        $model = Mage::getModel('fw_queue/queue');
        try{
            $model->load($id, 'queue_id');
            $model->setNumberAttempts(0);
            $model->save();
            $this->_getSession()->addSuccess('Successfully reset the queue');
        }catch(Exception $e){
            $this->_getSession()->addError('Could not reset the queue. ' . $e->getMessage());
        }
        $this->_redirect('*/*/list');
    }


    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('queue_id')) {
            try {

                $queue = Mage::getModel('fw_queue/queue');
                $queue->load($id);
                $queue->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fw_queue')->__("Item deleted from queue."));

                $this->_redirect('*/*/list');
                return;

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/list');
                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('eventqueue')->__('No queue item id specified. No record deleted.'));

        $this->_redirect('*/*/list');
    }

    public function massDeleteAction()
    {
        $queueIds = $this->getRequest()->getParam('queue_ids');

        if(!is_array($queueIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select queue item(s).'));
        } else {
            try {
                $items = Mage::getModel('fw_queue/queue')->getCollection();
                $items->addFieldToFilter('queue_id',array('in'=> $queueIds));
                $deleted = 0;

                foreach($items as $q){
                    $q->delete();
                    $deleted++;
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d queue record(s) were deleted.',$deleted));

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    public function massProcessAction()
    {
        $queueIds = $this->getRequest()->getParam('queue_ids');

        if(!is_array($queueIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select queue item(s).'));
        } else {
            try {
                $items = Mage::getModel('fw_queue/queue')->getCollection();
                $items->addFieldToFilter('queue_id',array('in'=> $queueIds));
                $processed = 0;

                foreach($items as $q){
                    $queue = Mage::getModel('fw_queue/queue')->load($q->getId());
                    $queue->process();
                    $processed++;
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d queue record(s) were processed.',$processed));

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
