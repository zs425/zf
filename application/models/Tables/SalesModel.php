<?php
class Table_SalesModel extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'sales_model';
	
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
}