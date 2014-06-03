<?php
class Table_LeadData extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'lead_data';
	
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
		'CampaignField' => array(
			'columns' => array('campaign_field_id'),
			'refTableClass' => 'Table_CampaignField',
			'refColumns' => array('id')
		)
	);
}
