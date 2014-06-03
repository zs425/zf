<?php
class Table_CampaignAdvertiser extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'campaign_advertiser';
	
	/**
	 * Clés primaires
	 * @var $_primary array
	 */
	protected $_primary = array('id', 'advertiser_id');
	
	/**
	 * Adapteur SQL
	 * 
	 * @var $_useAdapter string
	 */
	protected $_useAdapter = 'coregistration';
	
	/**
	 * Tableau des dépendances avec d'autres tables
	 * @var $_dependentTables array
	 */
	protected $_dependentTables = array('Table_Campaign');
}
