<?php
class Table_Publisher extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	//protected $_name = 'publisher';
	protected $_name = 'Liste_editeurs';
	
	/**
	 * Clé primaire
	 * @var $_primary string
	 */
	protected $_primary = 'ID';
	
	/**
	 * Adapteur SQL
	 * 
	 * @var $_useAdapter string
	 */
	protected $_useAdapter = 'baseandco';
	
	/**
	 * Tableau de références à d'autres table
	 * 
	 * @var $_referenceMap array
	 */
	/*protected $_referenceMap = array(
		'Category' => array(
			'columns' => array('category_id'),
			'refTableClass' => 'Table_Category',
			'refColumns' => array('id')
		),
		'Contact' => array(
			'columns' => array('contact_id'),
			'refTableClass' => 'Table_Contact',
			'refColumns' => array('id')
		)
	);*/
	
	/**
	 * Tableau des dépendances avec d'autres tables
	 * @var $_dependentTables array
	 */
	//protected $_dependentTables = array('Table_CampaignPublisher', 'Table_PublisherSalesModel');
	protected $_dependentTables = array('Table_CampaignPublisher');
	
	public function getCoregistered() {
		return $this->fetchAll("Modele_Achat LIKE '%Coregistration%'");
	}
	
}
