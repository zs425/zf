<?php
class Table_CampaignAdvertiserField extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'campaign_advertiser_field';
	
	/**
	 * Clés primaires
	 * @var $_primary array
	 */
	protected $_primary = array('field_id', 'advertiser_id');
	
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
	protected $_dependentTables = array('Table_CampaignField');
}
