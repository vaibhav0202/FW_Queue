<?php
    require_once '../abstract.php';
    /**
     * Magento Compiler Shell Script
     *
     * @category    Mage
     * @package     Mage_Shell
     * @author      Magento Core Team <core@magentocommerce.com>
     */
    class Mage_Shell_Compiler extends Mage_Shell_Abstract
    {
	/**
         * Run script
         *
         */
    	public function run()
    	{
		if( $this->getArg('help') || $this->getArg('h')) 
		{
			$this->usageHelp();
			return 0;
		}

    		Mage::app('admin');  // Run Mage app() and set scope to admin
		
		$queue = Mage::getSingleton('fw_queue/run');
    		$queue->autoPostOpenQueueItems();
	}

	/**
     	 * Retrieve Usage Help Message
     	 *
     	 */
    	public function usageHelp()
    	{
    	    return "Usage: php RunQueue.php\n\n";
        }
    }


$shell = new Mage_Shell_Compiler();
$shell->run();
