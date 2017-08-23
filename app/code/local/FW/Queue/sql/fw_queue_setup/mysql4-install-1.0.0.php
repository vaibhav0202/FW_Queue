<?php
$installer = $this;
$installer->startSetup();
$installer->run("
		DROP TABLE IF EXISTS `{$this->getTable('fw_queue')}`;
		CREATE TABLE IF NOT EXISTS `{$this->getTable('fw_queue')}` (
		  `queue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `code` varchar(255) DEFAULT '1',
		  `status` varchar(255) DEFAULT NULL,
		  `queue_item_data` text COMMENT 'Use to store data for callback method.',
		  `short_description` varchar(255) DEFAULT NULL,
		  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `created_at` datetime DEFAULT NULL,
		  `last_attempt` datetime DEFAULT NULL,
		  `number_attempts` int(6) DEFAULT NULL,
		  `is_manually_removed` int(2) DEFAULT NULL,
		  `method` varchar(255) NOT NULL COMMENT 'Callback method.',
		  `model_class` varchar(255) NOT NULL COMMENT 'Callback class, using Magento URI.',
		  `messages` text NOT NULL,
		  `errors` text NOT NULL,
		  PRIMARY KEY (`queue_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
$installer->endSetup();