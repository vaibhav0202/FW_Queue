<?php
class FW_Queue_Model_Queue extends Mage_Core_Model_Abstract
{
	const STATUS_OPEN = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_ERROR = 4;
    const STATUS_ABORTED_NOTIFIED = 5;
    
    protected $_messages = array();
    
    protected $_errors = array();
    
    protected $_queueData = array();
    
    protected function _construct()
	{
		parent::_construct();
		$this->_init('fw_queue/queue');
	}
	
	/**
	 * Get list of process status options
	 *
	 * @return array
	 */
	public function getStatusesOptions()
	{
		return array(
				self::STATUS_SUCCESS            => Mage::helper('fw_queue')->__('Successful'),
				self::STATUS_PROCESSING         => Mage::helper('fw_queue')->__('Processing'),
				self::STATUS_OPEN               => Mage::helper('fw_queue')->__('Open'),
				self::STATUS_ERROR            => Mage::helper('fw_queue')->__('Error'),
				self::STATUS_ABORTED_NOTIFIED   => Mage::helper('fw_queue')->__('Aborted And Notified'),
		);
	}
	
	/**
	 * @param $modelClass Magento model alias or class
	 * @param $method Call back method
	 * @param array $queueItemData
	 * @param string $code
	 * @param string $description
	 * @return FW_Queue_Model_Queue
	 * @throws Exception
	 */
	public function addToQueue($modelClass, $method, $queueItemData = array(), $code = '', $description = '')
	{
		if(!@class_exists($modelClass)){
			$modelClass = Mage::getConfig()->getModelClassName($modelClass);
		}
	
		if(!class_exists($modelClass)){
			throw new FW_Queue_Exception(sprintf('The callback modelClass or alias "%s" is not valid.',$modelClass));
		}
	
		if(!is_callable(array($modelClass,$method))){
			throw new FW_Queue_Exception(sprintf('The method %s::%s is not callable.',$modelClass,$method));
		}
	
		$this->setStatus(self::STATUS_OPEN);
		$this->setCode($code);
		$this->setShortDescription($description);
		$this->_queueData = $queueItemData;
		$this->setModelClass($modelClass);
		$this->setMethod($method);
		$this->setCreatedAt($this->getNow());
		$this->setLastAttempt(null);
		$this->setNumberAttempts(0);
		$this->save();
		return $this;
	}
	
	public function testAndSetLock()
	{
		return $this->getResource()->testAndSetLock($this);
	}
	
	public function releaseLock($status)
	{
		return $this->getResource()->releaseLock($this, $status);
	}
	
	/**
	 * @param array $data
	 */
	public function update(array $data = array())
	{
		if(!empty($data))
		{
			$this->addData($data);
		}
		$this->setNumberAttempts(($this->getNumberAttempts() + 1));
		$this->setLastAttempt($this->getNow());
		$this->incrementAttempts();
		$this->save();
	}
	
	public function changeStatus($status)
	{
		$this->setStatus($status);
		$this->save();
		return $this;
	}
	
	private function getNow()
	{
		$now = new DateTime('now');
		return $now->format('Y-m-d H:i:s');
	}
	
	public function incrementAttempts()
	{
		$this->setNumberAttempts(($this->getNumberAttempts() + 1));
		$this->save();
		return $this;
	}
	
	public function updateFailedAttempt()
	{
		// could be in a transactional
		$this->changeStatus(self::STATUS_ERROR);
		$this->incrementAttempts();
		$this->updateLastAttempt();
		return $this;
	}
	
	public function updateLastAttempt()
	{
		$this->setLastAttempt($this->getNow());
		$this->save();
		return $this;
	}
	
	public function updateSuccessfulAttempt()
	{
		// could be in a transactional
		$this->incrementAttempts();
		$this->updateLastAttempt();
		$this->changeStatus(self::STATUS_SUCCESS);
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getQueueData()
	{
		return $this->_queueData;
	}
	
	/**
	 * @param string $message The Exception message
	 * @param string|int $code The Exception error code, if meaningful
	 * @param boolean $systemMessage If TRUE the error message will be added to the system messages inbox.
	 * @param string $title The title of the error (Should be set if using in message inbox)
	 * @param int $severity The error severity level (4 - Notice, 3 - Minor, 2 - Major, 1 - Critical)
	 * @return FW_Queue_Model_Queue
	 */
	public function addError($message, $code = '',$systemMessage = false,$title = '',$severity = Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR)
	{
		$data = array(
				'code'      => $code,
				'title'     => $title,
				'message'   => $message,
				'timestamp' => $this->getResource()->formatDate($this->getTime('full')),
				'severity'  => $severity
		);
	
		$this->_errors[] = $data;
	
		if($systemMessage){
			$this->_addSystemMessage($data);
		}
		return $this;
	}
	
	/**
	 * @param string $message
	 * @param boolean $systemMessage If TRUE the error message will be added to the system messages inbox.
	 * @param string $title The title of the error (Should be set if using in message inbox)
	 * @return FW_Queue_Model_Queue
	 */
	public function addMessage($message,$systemMessage = false,$title = '')
	{
		$data = array(
				'title'     => $title,
				'message'   => $message,
				'timestamp' => $this->getResource()->formatDate($this->getTime('full')),
				'severity'  => Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE
		);
	
		$this->_messages[] = $data;
	
		if($systemMessage){
			$this->_addSystemMessage($data);
		}
		return $this;
	}
	
	/**
	 * Return error data arrays:
	 *
	 * - title
	 * - timestamp
	 * - message
	 * - severity
	 * - code
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
	
	/**
	 * Return message data arrays:
	 *
	 * - title
	 * - timestamp
	 * - message
	 * - severity
	 *
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_messages;
	}
	
	/*
	 * Add a message to the Magento system message inbox
	*
	* @param array $data
	* @return FW_Queue_Model_Queue
	*/
	protected function _addSystemMessage(array $data)
	{
		Mage::getModel('adminnotification/inbox')->parse(array(
		array(
		'severity'      => $data['severity'],
		'date_added'    => date('Y-m-d H:i:s'),
		'title'         => $data['title'],
		'description'   => $data['message'],
		'url'           => Mage::helper('adminhtml')->getUrl('adminhtml/fwqueue/edit', array('queue_id' => $this->getId())),
		'internal'      => true
		)
		));
	}
	
	/**
	 * @return int
	 */
	public function getProcessAttempts()
	{
		return $this->getNumberAttempts();
	}
	
	protected function _beforeSave()
	{
		$this->_data['messages'] = serialize($this->_messages);
		$this->_data['errors'] = serialize($this->_errors);
		$this->_data['queue_item_data'] = serialize($this->_queueData);
		return parent::_beforeSave();
	}
	
	
	protected function _afterLoad()
	{
		$this->_messages = $this->_getData('messages') ? unserialize($this->_getData('messages')) : array();
		$this->_errors = $this->_getData('errors') ? unserialize($this->_getData('errors')) : array();
		$this->_queueData = $this->_getData('queue_item_data') ? unserialize($this->_getData('queue_item_data')) : array();
		return parent::_afterLoad();
	}
	
	/**
	 * Get formated order created date in store timezone.
	 *
	 * @param   string $format date format type (short|medium|long|full)
	 * @return  string
	 */
	public function getTime($format)
	{
		$time = Mage::helper('core')->formatDate(Varien_Date::now(), $format, true);
		return $time;
	}
	
	/**
	 * Process a Queue Task. Uses the callback model and method found in the task record.
	 */
	public function process()
	{
		try {
			$modelClass = (string) $this->getModelClass();
			$method = (string) $this->getMethod();
			$model = Mage::getModel($modelClass);
	
			if(method_exists($model, $method)){
				call_user_func_array(array($model, $method), array($this));
				$this->updateSuccessfulAttempt();
			} else {
				$message = sprintf("%s is not a valid method.", $method);
				$this->updateFailedAttempt();
				$exception = new FW_Queue_Exception($message);
				Mage::logException($exception);
			}
		} catch(Exception $e) {
                        $this->addError($e->getMessage(),'',false,'Queue Error',Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL);
			$this->updateFailedAttempt();
		}
	}
}
