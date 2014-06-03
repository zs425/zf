<?php
class Table_CampaignPublisher extends BC_Db_Table_Abstract
{
	/**
	 * Nom de la table
	 * @var $_name string
	 */
	protected $_name = 'campaign_publisher';
	
	/**
	 * Clé primaire
	 * @var $_primary string
	 */
	protected $_primary = array('campaign_id', 'publisher_id');
	
	/**
	 * Adapteur SQL
	 * 
	 * @var $_useAdapter string
	 */
	protected $_useAdapter = 'coregistration';
	
	protected $_sequence = true;
	
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
		'Publisher' => array(
			'columns' => array('publisher_id'),
			'refTableClass' => 'Table_Publisher',
			'refColumns' => array('ID')
		)
	);
	
	public function getForActiveCampaigns() {
		$select = $this->select();
		$select->from('campaign_publisher', '*')
    		   ->join('campaign','campaign_publisher.campaign_id=campaign.id')
               ->where('campaign.status = :status');
               
        return $this->fetchAll($select, array('status'=>1) );
	}
}
