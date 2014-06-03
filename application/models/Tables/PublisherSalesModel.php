<?php
class Table_PublisherSalesModel extends BC_Db_Table_Abstract
{
	/**
     * Nom de la table
     *
     * @var string
     */
    protected $_name = 'publisher_sales_model';
    
    /**
     * Nom de la clé primaire
     *
     * @var array
     */
    protected $_primary = array('publisher_id' , 'sales_model_alias');
    
    /**
     * La clé primaire n'est pas auto-incrémentée
     *
     * @var bool
     */
    protected $_sequence = true;
    
    /**
     * Liaisons entre les tables
     *
     * @var array
     */
    protected $_referenceMap = array(
        'Publisher' => array(
	        'columns'           => 'publisher_id',
	        'refTableClass'     => 'Table_Publisher',
        ),
        'SalesModel' => array(
	        'columns'           => 'sales_model_alias',
	        'refTableClass'     => 'Table_SalesModel',
        )
    );
}
