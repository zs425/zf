<?php
class Table_Campaign extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * 
	 * @var $_name string
	 */
	protected $_name = 'campaign';
	
	/**
	 * Clé primaire
	 * 
	 * @var $_primary string
	 */
	protected $_primary = 'id';
	
	/**
	 * Adapteur SQL
	 * 
	 * @var $_useAdapter string
	 */
	protected $_useAdapter = 'coregistration';
	
	/**
	 * Tableau de références à d'autres table
	 * 
	 * @var $_referenceMap array
	 */
	protected $_referenceMap = array(
		'User' => array(
			'columns' => array('user_id'),
			'refTableClass' => 'Table_User',
			'refColumns' => array('id')
		),
		'CampaignAdvertiser' => array(
			'columns' => array('campaign_advertiser_id'),
			'refTableClass' => 'Table_CampaignAdvertiser',
			'refColumns' => array('id')
		),
		'Advertiser' => array(
			'columns' => array('campaign_advertiser_advertiser_id'),
			'refTableClass' => 'Table_CampaignAdvertiser',
			'refColumns' => array('advertiser_id')
		),
		'Target' => array(
			'columns' => array('target_id'),
			'refTableClass' => 'Table_Target',
			'refColumns' => array('id')
		)
	);
	
	/**
	 * Tableau des dépendances avec d'autres tables
	 * 
	 * @var $_dependentTables array
	 */
	protected $_dependentTables = array('Table_CampaignPublisher', 'Table_CampaignField');
}
