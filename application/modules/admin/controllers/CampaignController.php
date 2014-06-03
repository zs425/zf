<?php
class Admin_CampaignController extends BC_Controller_Action
{
	/**
	 * Table Campagnes
	 *
	 * @var Table_Campaign
	 */
	private $_campaignTable;

	/**
     * Table Campagne-Annonceur
     *
     * @var Table_CampaignAdvertiser
     */
    private $_campaignAdvertiserTable;

    /**
     * Table Campagne-Champs
     *
     * @var Table_CampaignField
     */
    private $_campaignFieldsTable;

	public function init() {
		$this->_campaignTable = new Table_Campaign();
		$this->_campaignAdvertiserTable = new Table_CampaignAdvertiser();
		$this->_campaignFieldsTable = new Table_CampaignField();
		$this->_campaignPublisherTable = new Table_CampaignPublisher();
	}

	public function indexAction() {
		$this->_redirect($this->_helper->url('list', 'campaign', 'admin'));
	}

	/* 
		Listing all campaigns
	*/
	public function listAction() {
		$messages = $this->_helper->FlashMessenger->getMessages();

		$this->view->sortBy = $this->_getParam('sortBy') == 'name' ? 'name':'id';

		if (!empty($messages)) {
			$this->view->message = $messages[0];
		}
		$params = $this->getRequest()->getParams();
		$this->view->filter = false;
		if (isset($params['filter'])) {
			if ($params['filter'] == 'archived') {
				$this->view->filter = true;
				$this->view->setTitrePage("Campagnes archivées");
				$this->view->campaigns = $this->_campaignTable->fetchAll('status = 0', $this->_getParam('sortBy'));
			}
		} else {
			$this->view->setTitrePage("Campagnes actives");
			$this->view->campaigns = $this->_campaignTable->fetchAll('status = 1', $this->_getParam('sortBy'));
		}
	}

	/* 
		Viewing a selected campaign
	*/
	public function viewAction() {
		$id = (int)$this->_getParam('c');
		$this->view->campaign = $this->_campaignTable->find($id)->current();
		$this->view->campaignPublishers = $this->_campaignPublisherTable->fetchAll('campaign_id = ' . $id);
		$this->view->setTitrePage("Campagne: " . $this->view->campaign->name);
	}

	/* 
		tesing the FTP account associated with the campaign
	 	the function on the page opens up a new window the the FTP connection is tested
	*/
	public function testftpAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout()->disableLayout();

		$params = $this->getRequest()->getParams();
		$cid = (int)$params['c'];

		$ftp = new BC_FtpUploader();
		$message = $ftp->testFtpConnection($cid);

		$this->view->ftpMessage = $message;

		echo $message;
	}
	
	/*
		Editing an existing campaign
	*/
	public function editAction() {
		$params = $this->getRequest()->getParams();
		$isUpdate = isset($params['c']);

		//$this->view-> = $this->_helper->url('edit', 'webservice', 'admin', array('c' => $campaignId, 'p' => $publisherId));
		
		/*
			Link to the function that is testing the FTP connection
		*/
		$this->view->ftpTestUrl = $this->_helper->url('testftp', 'campaign', 'admin', array('c' => $params['c']));

		$messages = $this->_helper->FlashMessenger->getMessages();
		if (!empty($messages)) {
			$this->view->message = $messages[0];
		}

		/*
			Updating an existing campaign
		*/
		if ($isUpdate) {
		    $params['c'] = (int)$params['c'];
		    $this->view->setTitrePage("Editer une campagne");
			$this->view->campaign_id = $params['c'];
		    $campaign = $this->_campaignTable->find((int)$params['c'])->current();
		    $campaignAdvertiser = $campaign->findParentTable_CampaignAdvertiser();
		    $campaignFields = array();
		    $position = 1;

			/*
				Repositioning the fields associated with the campaign in case they have been modified
			*/
			foreach ($this->_campaignFieldsTable->fetchAll('campaign_id = ' . (int)$params['c']) as $field) {
				$campaignFields[$field['field_alias']] = $position;
				$position++;
			}

		} else { // Creating a new campaign
		    $this->view->setTitrePage("Créer une campagne");
		    $campaign = $this->_campaignTable->createRow();
		    //$campaign->status = '1';
			$campaign->status = '1';
		    $campaignAdvertiser = $this->_campaignAdvertiserTable->createRow();
		    $campaignFields = array();
		}

		/*
			Creating the form
		*/
		$form = new Form_Campaign();
		$form->setAction($this->view->link('campaign' , 'edit', 'admin', $isUpdate ? array('c' => (int)$params['c']) : '', 'default', !$isUpdate))
		     ->setMethod('post');
		$form->getSubForm('campaignForm')->setDefaults($campaign->toArray());
		$form->getSubForm('campaignAdvertiserForm')->setDefaults($campaignAdvertiser->toArray());
		$form->getSubForm('campaignFieldsForm')->setDefaults($campaignFields);

		if ($this->getRequest()->isPost() && $form->isValid($_POST) && !$params['testftp']) {
			$advValues = $form->getSubForm('campaignAdvertiserForm')->getValues();

			/*
				Serializing the array created by the MultipleCheckbox (for the days "lundi", "mardi" etc. ) element in the form, 
				so it can be stored in the database as a test string.
			*/
			$advValues['campaignAdvertiserForm']['send_day'] = serialize($advValues['campaignAdvertiserForm']['send_day']);
			$advValues['campaignAdvertiserForm']['send_time'] = str_replace("h", "", $advValues['campaignAdvertiserForm']['send_time']);

			$campaignAdvertiserId = $this->_campaignAdvertiserTable->insert($advValues['campaignAdvertiserForm']);
			$cpgValues = $form->getSubForm('campaignForm')->getValues();

			if (!$isUpdate) {
			    $cpgValues['campaignForm']['creation_date'] = date('y-m-d H:m:s');
			} elseif ($campaign->status != $_POST["campaignForm"]["status"]) {
				$webservices = $campaign->findTable_CampaignPublisher();
				foreach ($webservices as $webservice) {
					if ($webservice->status != -1) {
						$webservice->status = $_POST["campaignForm"]["status"];
						$webservice->save();
					}
				}
			}

		    $cpgValues['campaignForm']['lastupdate_date'] = date('y-m-d H:m:s');
		    $cpgValues['campaignForm']['campaign_advertiser_id'] = $campaignAdvertiserId['id'];
		    $cpgValues['campaignForm']['campaign_advertiser_advertiser_id'] = $advValues['campaignAdvertiserForm']['advertiser_id'];

			/*
				Converting the French date format to Swedish date format YYYY-MM-DD
			*/
			$date = new Zend_Date();
			$cpgValues['campaignForm']['start_date'] = $date->set( $cpgValues['campaignForm']['start_date'], 'DD/MM/YYYY')->toString('YYYY-MM-dd');
			$cpgValues['campaignForm']['end_date'] = $date->set( $cpgValues['campaignForm']['end_date'], 'DD/MM/YYYY')->toString('YYYY-MM-dd');

		    $campaign->setFromArray(array_intersect_key($cpgValues['campaignForm'], $campaign->toArray()));

		    $campaignId = $campaign->save();

			/*
				Selecting a default publisher
			*/
			if (!$isUpdate) {
				$this->_campaignPublisherTable->insert(array('campaign_id' => $campaignId, 'publisher_id' => 2));
			}

			/*
				Listing of the form fields associated with the campaign, updating them and removes unused ones
			*/
		    try{
				$fieldsValues = $form->getSubForm('campaignFieldsForm')->getValues();
				asort($fieldsValues['campaignFieldsForm']);

				// Getting all the fiels associated with the selected campaign
				$campaignFieldsTable = new Table_CampaignField();
				$rows = $campaignFieldsTable->fetchAll($campaignFieldsTable->select()->where('campaign_id = ?', $campaignId));
				$fieldsInCampaign = $rows->toArray();
				$usedIds = array();
				$counter = 0;

				/*
					Loops all the fields that have a value in the form. The empty fields are not concerned.
					If an id exist, it is updated otherwise it is inserted as new.
				*/
				foreach ($fieldsValues['campaignFieldsForm'] as $k => $v) {
					if ($v > 0) {

						// If an used id is available then update
						if($counter < sizeof($fieldsInCampaign)){
							$data = array('field_alias' => $k);
							$where = array('id = '.$fieldsInCampaign[$counter]['id']);
							$usedIds[] = $fieldsInCampaign[$counter]['id'];
						}else{
							// If an used id is ot available then insert
							$this->_campaignFieldsTable->insert(array('campaign_id' => $campaignId, 'field_alias' => $k));
						}

						$counter++;
					}
				}

				/*
					Deleting ids not used (empty fields in the form)
				*/
				foreach($fieldsInCampaign as $row){
					if(!in_array($row['id'], $usedIds)){
						$this->_campaignFieldsTable->delete('id = ' . $row['id']);
					}
				}

				/*
					Redirecting to the Web services page so that the matched fields can be verified
				*/
				$publisher = $this->_campaignPublisherTable->fetchAll('campaign_id = ' . $campaignId)->toArray();
				$publisherId = $publisher[0]['publisher_id'];
				$flashMessenger = $this->_helper->FlashMessenger;
				$message = "Si vous avez modifié les champs, controllez aussi le matching des champs du client";
				$flashMessenger->addMessage($message);

				$this->_redirect($this->_helper->url('edit', 'webservice', 'admin', array('c' => $campaignId, 'p' => $publisherId)));

			}catch(Exception $e){
				$this->_helper->FlashMessenger->addMessage("Impossible de modifier les champs car cette campagne comporte déjà des leads. Les autres informations ont été sauvegardées.");
			}

			$this->_redirect($this->_helper->url(null, 'campaign', 'admin'));
		}

			/*
				Unserializing the string into an array so the MultipleCheckbox element in the form
				can be filled out with data
			*/
			$tempVar = unserialize($form->getSubForm('campaignAdvertiserForm')->getValue('send_day'));
			$form->getSubForm('campaignAdvertiserForm')->getElement('send_day')->setValue($tempVar);

			$this->view->form = $form;
			$this->view->advertiserForm = new Form_Advertiser();
	}

	/*
		Archiving a campaign
	*/
	public function archiveAction() {
		$params = $this->getRequest()->getParams();
		if (isset($params['c'])) {
			$campaign = $this->_campaignTable->find($params['c'])->current();
			if (isset($params['confirm'])) {
				if ($params['confirm'] == 'do') {
					$webservices = $campaign->findTable_CampaignPublisher();
					foreach ($webservices as $webservice) {
						$webservice->status = 0;
						$webservice->save();
					}
					$campaign->status = 0;
					$campaign->save();
					$this->_helper->FlashMessenger->addMessage("La campagne '" . $campaign->name . "' a bien été archivée.");
					$this->_redirect($this->_helper->url(null, 'campaign', 'admin'));
				}
			} else {
				$this->view->campaign = $campaign;
			}
		} else {
			$this->_redirect($this->_helper->url(null, 'campaign', 'admin'));
		}
	}

	/*
		Activating a campaign (from "Archived" state)
	*/
	public function activeAction() {
		$params = $this->getRequest()->getParams();
		if (isset($params['c'])) {
			$campaign = $this->_campaignTable->find($params['c'])->current();
			if (isset($params['confirm'])) {
				if ($params['confirm'] == 'do') {
					$webservices = $campaign->findTable_CampaignPublisher();
					foreach ($webservices as $webservice) {
						$webservice->status = 1;
						$webservice->save();
					}
					$campaign->status = 1;
					$campaign->save();
					$this->_helper->FlashMessenger->addMessage("La campagne '" . $campaign->name . "' a bien été activée.");
					$this->_redirect($this->_helper->url(null, 'campaign', 'admin'));
				}
			} else {
				$this->view->campaign = $campaign;
			}
		} else {
			$this->_redirect($this->_helper->url(null, 'campaign', 'admin'));
		}
	}

	/*
		This function is probably not used.
		Better to leave it for the moment.
	*/
	public function sendMailAction(){

		$params = $this->getRequest()->getParams();

		$messages = $this->_helper->FlashMessenger->getMessages();
		if (!empty($messages)) {
			$this->view->message = $messages[0];
		}

		if (isset($params['c'])) {

			$cid = $params['c'];
			$startDate = $params['start_date'];
			$endDate = $params['end_date'];
			$sendTime = $params['send_time'];
			$endTime = $params['end_time'];

			$campaign = $this->_campaignTable->find($params['c'])->current();

			$form = new Form_StatisticsMail();
			$form->setAction($this->view->link('campaign' , 'send-mail', 'admin', array('c' => (int)$params['c'])))
				 ->setMethod('post');

			$this->view->setTitrePage("Envoyer un mail avec des statistics");
			$this->view->campaign = $campaign->name;
			$this->view->form = $form;

			if ($this->getRequest()->isPost() && $form->isValid($_POST) && isset($params['start_date']) && isset($params['end_date'])) {

				//print_r($params);
				$date = new Zend_Date();
				$startDate = $date->set($startDate, 'DD/MM/YYYY')->toString('y-MM-dd');
				$endDate = $date->set($endDate, 'DD/MM/YYYY')->toString('y-MM-dd');
				$sendTime = str_replace("h", "", $sendTime);
				$endTime = str_replace("h", "", $endTime);

				$se = new BC_StatisticsExporter();
				$se->sendSingleStatisticsMail($cid, $startDate, $endDate, $sendTime, $endTime);

				$this->view->hasError = false;
				$this->view->message = "Email envoyé";
			}

		} else {
			$this->_redirect($this->_helper->url(null, 'campaign', 'admin'));
		}
	}

}
