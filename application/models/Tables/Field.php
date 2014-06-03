<?php
class Table_Field extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'field';
	
	/**
	 * Clé primaire
	 * @var $_primary string
	 */
	protected $_primary = 'alias';
	
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