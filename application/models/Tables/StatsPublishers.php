<?php
class Table_StatsPublishers extends BC_Db_Table_Abstract
{
	protected $_name = 'stats_publishers';
	
	protected $_primary = 'publisher_id';
	
	/**
	 * Adapteur SQL
	 * 
	 * @var $_useAdapter string
	 */
	protected $_useAdapter = 'coregistration';
}