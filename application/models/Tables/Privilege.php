<?php
class Table_Privilege extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * 
	 * @var $_name string
	 */
	protected $_name = 'privilege';
	
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
		'Role' => array(
			'columns' => array('resource_alias'),
			'refTableClass' => 'Table_Contact',
			'refColumns' => array('alias')
		),
		'Action' => array(
			'columns' => array('action_alias'),
			'refTableClass' => 'Table_Contact',
			'refColumns' => array('alias')
		),
		'Resource' => array(
			'columns' => array('resource_alias'),
			'refTableClass' => 'Table_Contact',
			'refColumns' => array('alias')
		)
	);
}