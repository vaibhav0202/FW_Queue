<?php
class FW_Queue_Model_Run {

	/**
	* CRON EVENT FUNCTION THAT GATHERS OPEN AND ERROR QUEUE SUBMISSIONS
	* AND RE-PROCESSESS THEM
	*
	*/
	public function autoPostOpenQueueItems()
	{
		$helper = Mage::helper('fw_queue');
		if($helper->isQueueEnabled())
		{
			$start = -microtime(true);
			$collection = Mage::getModel('fw_queue/queue')->getCollection();
			$collection->addFieldToSelect('*');
			$collection->addFieldToFilter('status', array(array('eq' => '1'),array('eq' => '4')));
			foreach($collection as $queue_item){
				$queue = Mage::getModel('fw_queue/queue')->load($queue_item->getId());
				$queue->process();
			}
			//CLEAN-UP AND STOP ERROR QUEUE ITEMS
			$expired = Mage::getModel('fw_queue/queue')->getCollection();
			$expired->addFieldToSelect('*');
			$expired->addFieldToFilter('status', array(array('eq' => '4')));
			$expired->addFieldToFilter('number_attempts', array(array('gteq' => '75')));
			foreach($expired as $expire_item){
				$queue = Mage::getModel('fw_queue/queue')->load($queue_item->getId());
				//STATUS_ABORTED_NOTIFIED = 5
				$queue->changeStatus('5');
			}

			//CLEAN-UP OLD ITEMS
			$date = date('Y-m-d H:i:s', time());
			$queueLastAttemptDate = strtotime ( '-90 day' , strtotime ( $date ) ) ;
			$queueLastAttemptDate = date ( 'Y-m-d H:i:s' , $queueLastAttemptDate );

			try {
				$queueItems = Mage::getModel('fw_queue/queue')
				->getCollection()
				->addFieldToSelect('*')
				->addFieldToFilter('last_attempt', array('to' => $queueLastAttemptDate));

				$queueItems->getSelect()->limit(10000);

				foreach ($queueItems as $queueItem)
				{
					$queueItem->delete();
				}
			}
			catch(Exception $e){
				Mage::logException($e->getMessage());
			}
			$totalTime = microtime(true) + $start;
			$span = gmdate("H:i:s",$totalTime);
			$micro = substr($totalTime - floor($totalTime),2);
			$logLine = "Queue executed in {$span}.{$micro}\r\n";
			Mage::Log($logLine,null,'fw_queue.log');

			//Dispatch Event to let fw_orderpublish know when queue is done running
			$eventData = array('queue_complete' => 'true');
			Mage::dispatchEvent('fw_queue_run_complete');
		}

	}
}
