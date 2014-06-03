<?php
class Table_User extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'user';
	
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
		'Role' => array(
			'columns' => array('role_alias'),
			'refTableClass' => 'Table_Role',
			'refColumns' => array('alias')
		)
	);
	
	/**
	 * Tableau des dépendances avec d'autres tables
	 * @var $_dependentTables array
	 */
	protected $_dependentTables = array('Table_Campaign');
}
