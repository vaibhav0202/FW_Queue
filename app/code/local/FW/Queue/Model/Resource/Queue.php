<?php
class FW_Queue_Model_Resource_Queue extends Mage_Core_Model_Mysql4_Abstract{
	protected function _construct()
	{
		$this->_init('fw_queue/queue', 'queue_id');
	}
	
	public function testAndSetLock(FW_Queue_Model_Queue $queue)
	{
	
		$writeAdapater = $this->_getWriteAdapter();
		$writeAdapater->beginTransaction();
		$select = $writeAdapater->select();
		$select->from($this->getMainTable(), 'status')
		->where('queue_id = ?', $queue->getId())
		->forUpdate();
		$all_results = $writeAdapater->fetchAll($select);
		$results = array_shift($all_results);
	
		if(isset($results['status']) && $results['status'] !== FW_Queue_Model_Queue::STATUS_PROCESSING)
		{
			try{
				$writeAdapater->update(
						$this->getMainTable(),
						array('status' => FW_Queue_Model_Queue::STATUS_PROCESSING),
						array('queue_id = ?' => $queue->getId())
				);
				$writeAdapater->commit();
			}catch(Exception $e){
				Mage::logException($e);
				$writeAdapater->rollBack();
				return false;
			}
			return true;
		}else{
			$writeAdapater->rollBack();
			return false;
		}
	}
	
	public function releaseLock(FW_Queue_Model_Queue $queue, $status)
	{
		$writeAdapater = $this->_getWriteAdapter();
		$writeAdapater->beginTransaction();
		$select = $writeAdapater->select();
		$select->from($this->getMainTable(), 'status')
		->where('queue_id = ?', $queue->getId())
		->forUpdate();
		$results = $writeAdapater->fetchAll($select);
		if(isset($results['status']) && $results['status'] === FW_Queue_Model_Queue::STATUS_PROCESSING)
		{
			try{
				$writeAdapater->update(
						$this->getMainTable(),
						array('status' => $status),
						array('queue_id = ?' => $queue->getId())
				);
				$writeAdapater->commit();
				return true;
			}catch(Exception $e){
				Mage::logException($e);
				$writeAdapater->rollBack();
				return false;
			}
		}else{
			$writeAdapater->rollBack();
			return false;
		}
	}
}