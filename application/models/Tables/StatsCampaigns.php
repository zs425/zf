<?php
class Table_StatsCampaigns extends BC_Db_Table_Abstract
{
	protected $_name = 'stats_campaigns';
	
	protected $_primary = 'campaign_id';
	
	/**
	 * Adapteur SQL
	 * 
	 * @var $_useAdapter string
	 */
	protected $_useAdapter = 'coregistration';
	
}
