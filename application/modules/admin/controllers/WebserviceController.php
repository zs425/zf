<?php
class Admin_WebserviceController extends BC_Controller_Action
{
	/**
	 * Table CampaignPublisher
	 *
	 * @var Table_CampaignPublisher
	 */
	private $_campaignPublisherTable;
	private $_campaignAdvertiserFieldsTable;

    /*
    	Sending a mail to publisher with information about the web service
    */
    protected function _sendMail($campaignPublisher)
    {
        $m = new BC_HtmlMailer();
        $m->setSubject('Création Campagne - Coregistration');

        $m->addTo($campaignPublisher->findParentTable_Campaign()->findParentTable_User()->email);
        $m->addCc($campaignPublisher->email);

        $m->setViewParam('editor', $campaignPublisher->findParentTable_Publisher()->Contact);
        $m->setViewParam('webservice', $campaignPublisher);
        $m->setViewParam('fields', $campaignPublisher->findParentTable_Campaign()->findTable_CampaignField());
        $m->setViewParam('passkey', md5($campaignPublisher->campaign_id . '&' . $campaignPublisher->publisher_id));
        $m->setViewParam('campaign', $campaignPublisher->findParentTable_Campaign());
        $m->sendHtmlTemplate('editor.phtml');
    }

    public function testMailAction()
    {
        $campaignPublisher = $this->_campaignPublisherTable->find(6, 694)->current();
        $this->_sendMail($campaignPublisher);
    }

    public function init() {
		$this->_campaignPublisherTable = new Table_CampaignPublisher();
		$this->_campaignAdvertiserFieldsTable = new Table_CampaignAdvertiserField();
		$this->_testDataTable = new Table_TestData();
		$this->_userTable = new Table_User();
	}

	public function indexAction() {
		$this->_redirect($this->_helper->url('list', 'webservice', 'admin'));
	}

	/*
		Listing the web services.
		They are separated by "Active" och "Archived"
	*/
	public function listAction() {

        $status = 1;
        if($this->_hasParam('filter'))
        {
            $status = 0;
            $this->view->filter = true;
        }

		$this->view->publisherId = $this->_getParam('publisherId');
		$this->view->campaignId = $this->_getParam('campaignId');

		$this->view->setTitrePage("Webservices");
		$messages = $this->_helper->FlashMessenger->getMessages();

        $this->view->sortBy = $this->_getParam('sortBy') == 'publisher' ? 'publisher_id':'campaign_id';

        if (!empty($messages)) {
	        $this->view->message = $messages[0];
        }

		/*
			Getting the publisher name and id
		*/		
		$sql = "SELECT sp.publisher_id, sp.name
				FROM stats_publishers sp
				INNER JOIN campaign_publisher cp ON cp.publisher_id = sp.publisher_id
				GROUP BY sp.publisher_id
				ORDER BY sp.name ASC";

		$db = Zend_Db_Table::getDefaultAdapter();
		$this->view->publishers = $db->query($sql);

		/*
			Getting the campaign name and id
		*/
		$sql = "SELECT c.id, c.name
				FROM stats_publishers sp
				INNER JOIN campaign c ON c.id = sp.campaign_id
				GROUP BY c.id
				ORDER BY c.name ASC";

		$db = Zend_Db_Table::getDefaultAdapter();
		$this->view->campaigns = $db->query($sql);

		if($this->_getParam('publisherId')){
			$where = "publisher_id=".$this->_getParam('publisherId');
			$this->view->campaignPublishers = $this->_campaignPublisherTable->fetchAll($where);
		}elseif($this->_getParam('campaignId')){
			$where = "campaign_id=".$this->_getParam('campaignId');
			$this->view->campaignPublishers = $this->_campaignPublisherTable->fetchAll($where);
		}else{
			$this->view->campaignPublishers = $this->_campaignPublisherTable->fetchAll('status=' . $status, $this->view->sortBy . ' ASC');
		}
	}

	/*
		Web service information page
	*/
	public function viewAction() {
		$params = $this->getRequest()->getParams();
		$this->view->setTitrePage("Voir un webservice");
        if (isset($params['c']) && isset($params['p'])) {
        	$campaignPublisher = $this->_campaignPublisherTable->find((int)$params['c'], (int)$params['p'])->current();
        }
	}

	/*
		Creating a new web service
		
	*/
	public function newAction() {

        $this->view->setTitrePage("Créer un webservice");
        $campaignPublisher = $this->_campaignPublisherTable->createRow();

        $form = new Form_Webservice();
        $form->setAction($this->view->link('webservice' , 'new', 'admin'))
             ->setMethod('post')
             ->setDefaults($campaignPublisher->toArray());

        if ($this->getRequest()->isPost()) {
        	if ($form->isValid($_POST)) {
        		$values = $form->getValues();
				$values['creation_date'] = date('y-m-d H:m:s');

	            $campaignPublisher->setFromArray(array_intersect_key($values, $campaignPublisher->toArray()));
	            $campaignPublisher->save();

				$this->_sendMail($campaignPublisher);

	            $flashMessenger = $this->_helper->FlashMessenger;
			    $message = "Le webservice a bien été créé.";

			    $flashMessenger->addMessage($message);

	            $this->_redirect($this->_helper->url(null, 'webservice', 'admin'));
        	} else {
        		// form not valid
        	}
        }
        $messages = $this->_helper->FlashMessenger->getMessages();

        if (!empty($messages)) {
	        $this->view->message = $messages[0];
        }

        $this->view->form = $form;
	}

	/*
		Editing a web service
	*/
	public function editAction() {
		$params = $this->getRequest()->getParams();
        $isUpdate = isset($params['c']) && isset($params['p']);

        /*
        	Getting the advertiser id
        */
        $db = Zend_Db_Table::getDefaultAdapter();
		$stmt = $db->query("SELECT ca.advertiser_id
							FROM campaign_advertiser ca
							INNER JOIN  campaign c
							ON c.campaign_advertiser_id = ca.id
							WHERE c.id = ".$params['c']);

		$advertiser = $stmt->fetch();
		$advertiserId = $advertiser['advertiser_id'];

        /*
        	Getting advertisers own field names used for the current campaign
        */
		$db = Zend_Db_Table::getDefaultAdapter();
		$stmt = $db->query("SELECT caf.field_id, caf.advertiser_field
							FROM campaign_advertiser_field caf
							INNER JOIN (
							SELECT cf.id, cf.field_alias
							FROM campaign_field cf
							WHERE cf.campaign_id = ".$params['c'].") campaign_fields
							ON campaign_fields.id = caf.field_id
							WHERE caf.advertiser_id=".$advertiserId);

		$campaignAdvertiserFields = $stmt->fetchAll();

        /*
        	If not update then create a new web service
        */
        if ($isUpdate) {
            $this->view->setTitrePage("Editer un webservice");
            $campaignPublisher = $this->_campaignPublisherTable->find((int)$params['c'], (int)$params['p'])->current();

        } else {
            $this->view->setTitrePage("Créer un webservice");
            $campaignPublisher = $this->_campaignPublisherTable->createRow();
        }

        $form = new Form_Webservice();
        $form->setAction($this->view->link('webservice' , 'edit', 'admin', $isUpdate ? array('c' => (int)$params['c'], 'p' => (int)$params['p']) : '', 'default', !$isUpdate))
             ->setMethod('post')
             ->setDefaults($campaignPublisher->toArray());
		
		/*
			Formats the data correctly so it can be written to the form "fieldMatchingForm".
        	The field names in the form are having ids as id/name
        */
        $matchedFields = array();
		foreach($campaignAdvertiserFields as $campaignAdvertiserField){
			$id = $campaignAdvertiserField['field_id'];
			$matchedFields[$id] = $campaignAdvertiserField['advertiser_field'];
		}
        
        /*
        	Writing the data to the form "fieldMatchingForm"
        */
        $form->getSubForm('fieldMatchingForm')->setDefaults($matchedFields);

        if ($this->getRequest()->isPost()) {
        	if (!$isUpdate && $this->_campaignPublisherTable->find((int)$params['campaign_id'], (int)$params['publisher_id'])->current() != null) {
        		$flashMessenger = $this->_helper->FlashMessenger;
        		$flashMessenger->addMessage("Le webservice existe déjà.");
        		$this->_redirect($this->view->link('webservice', 'edit', 'admin', array('c' => $params['campaign_id'], 'p' => $params['publisher_id'])));
        		exit;
        	} elseif ($form->isValid($_POST)) {

        		$values = $form->getValues();

	            /*if (!$isUpdate) {
	            	$values['creation_date'] = date('y-m-d H:m:s');
	            }*/

	            $campaignPublisher->setFromArray(array_intersect_key($values, $campaignPublisher->toArray()));
	            $campaignPublisher->save();

	            /*
	            	Updates the Campaign Advertiser_Field table
	            */
	            $fieldsValues = $form->getSubForm('fieldMatchingForm')->getValues();
				asort($fieldsValues['fieldMatchingForm']);

				$this->_campaignAdvertiserFieldsTable->delete('advertiser_id = '.$advertiserId);

	            foreach ($fieldsValues['fieldMatchingForm'] as $k => $v) {
            		if($v != "")
	            		$this->_campaignAdvertiserFieldsTable->insert(array('advertiser_id' => $advertiserId, 'field_id' => $k, 'advertiser_field' => $v));
	            }

				/*
					The field with the web service is mandatory if the fields have been matched
				*/
				if (array_filter($fieldsValues['fieldMatchingForm']) && $form->getValue("webservice_url") == ""){
					$flashMessenger = $this->_helper->FlashMessenger;
					$flashMessenger->addMessage("Le champ Webservice Client est obligatoire si les champs ont été assoicés.");
					$this->_redirect($this->view->link('webservice', 'edit', 'admin', array('c' => $params['campaign_id'], 'p' => $params['publisher_id'])));
				}

	            /*if (!$isUpdate) {
                    $this->_sendMail($campaignPublisher);
	            }*/

				if($campaignPublisher->creation_date == "0000-00-00 00:00:00"){
					$campaignPublisher->creation_date = date('Y-m-d H:i:s');
					$campaignPublisher->save();
					$this->_sendMail($campaignPublisher);
				}

	            $flashMessenger = $this->_helper->FlashMessenger;
			    $message = "Le webservice a bien été ";
			    if ($isUpdate) {
			    	$message .= "modifié.";
			    } else {
			    	$message .= "créé.";
			    }
			    $flashMessenger->addMessage($message);

	            $this->_redirect($this->_helper->url(null, 'webservice', 'admin'));
        	} else {
        		// form not valid
        	}
        }
        $messages = $this->_helper->FlashMessenger->getMessages();

        if (!empty($messages)) {
	        $this->view->message = $messages[0];
        }

        $this->view->form = $form;
	}

	public function editTestDataAction() {

		$messages = $this->_helper->FlashMessenger->getMessages();

		if (!empty($messages)) {
			$this->view->message = $messages[0];
		}

		$id = $this->_getParam('id');
		$action = $this->_getParam('a');

		if($action == "delete"){
			$testData = $this->_testDataTable->delete('id = ' . $id);
			$this->_redirect($this->_helper->url('edit-test-data', 'webservice', 'admin'));
		}

		$this->view->setTitrePage("Gérer la liste des données de test");
		$testData = $this->_testDataTable->fetchAll();

        $form = new Form_TestData();
        $form->setAction($this->view->link('webservice' , 'edit-test-data', 'admin'))
             ->setMethod('post');

        if ($this->getRequest()->isPost()) {
        	if ($form->isValid($_POST)) {
				$testDataValues = $form->getValues();

				$testData = $this->_testDataTable->createRow();
				$testData->data = $testDataValues['data'];
				$testData->save();

				$this->_redirect($this->_helper->url('edit-test-data', 'webservice', 'admin'));
			}
        }

        $this->view->testData = $testData;
		$this->view->form = $form;
	}
}
