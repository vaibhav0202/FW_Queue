<?php
$installer = $this;
$connection = $installer->getConnection();
$tableName = $installer->getTable('fw_queue');
$indexNameToCreate = $installer->getIdxName($tableName, array('status'));
$connection->addIndex($tableName, $indexNameToCreate, array('status'));
