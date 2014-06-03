<?php
class Table_CampaignField extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'campaign_field';
	
	/**
	 * Clé primaire
	 * @var $_primary string
	 */
	protected $_primary = 'id';
	
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
		'Field' => array(
			'columns' => array('field_alias'),
			'refTableClass' => 'Table_Field',
			'refColumns' => array('alias')
		)
	);
}
