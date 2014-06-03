<?php
class Table_TestData extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'test_data';
	
	/**
	 * Clé primaire
	 * @var $_primary string
	 */
	protected $_primary = 'data';
	
	/**
	 * Adapteur SQL
	 * 
	 * @var $_useAdapter string
	 */
	protected $_useAdapter = 'coregistration';
	
}
