<?php
class Table_Lead extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'lead';
	
	/**
	 * Clé primaire
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
		'Campaign' => array(
			'columns' => array('campaign_id'),
			'refTableClass' => 'Table_Campaign',
			'refColumns' => array('id')
		),
		'LeadData' => array(
			'columns' => array('id'),
			'refTableClass' => 'Table_LeadData',
			'refColumns' => array('lead_id')
		)
	);
}
