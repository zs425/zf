<?php
class Table_Advertiser extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * 
	 * @var $_name string
	 */
	protected $_name = 'advertiser';
	
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
		'Contact' => array(
			'columns' => array('contact_id'),
			'refTableClass' => 'Table_Contact',
			'refColumns' => array('id')
		)
	);
	
	/**
	 * Tableau des dépendances avec d'autres tables
	 * @var $_dependentTables array
	 */
	protected $_dependentTables = array('Table_CampaignAdvertiser');
}