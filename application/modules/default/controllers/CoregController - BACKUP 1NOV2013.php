<?php
class CoregController extends Zend_Controller_Action
{
	public function indexAction()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout()->disableLayout();
    	$params = $this->getRequest()->getParams();
		
        if (isset($params['cid']) && isset($params['pid']) && isset($params['passkey'])) {

        	if ($this->getRequest()->isPost()) {
	        	$values = $_POST;
        	} elseif ($this->getRequest()->isGet()) {
        		$values = $_GET;
        	}

			$values = array_change_key_case($values, CASE_LOWER);

			$debug = $params['debug'];

        	// Récupération de l'id de campagne
        	$cid = $params['cid'];
        	unset($values['cid']);

        	// Récupération de l'id de l'éditeur
        	$pid = $params['pid'];
        	unset($values['pid']);

        	// Récupération du hash
        	$passkey = $values['passkey'];
	        unset($values['passkey']);
			
			$valuesForUnitMail = $values;

	        if ($passkey != md5($cid . '&' . $pid)) {
	        	die("Mauvaise combinaison d'identifiants");
	        }

        	$TCampaign = new Table_Campaign();
        	$campaign = $TCampaign->find($cid)->current();

			if(sizeof($values) == 0)
			{
				die('NO VARIABLES USED');
			}

        	if ($campaign !== NULL) {
        		if ($campaign->status == '0') {
        			die ("Campagne archiv&eacute;e !");
        		}

        		$email = $values['email'];

                //Insertion des données pour chaque champs
//	        	foreach ($campaignFields as $campaignField) {
//                    if(isset($values[$campaignField['field_alias']]))
//                    {
//                        $TLeadData->insert(array(
//                            'lead_id' => $leadId,
//                            'value' => $values[$campaignField['field_alias']],
//                            'record_date' => date('Y-m-d H:i:s'),
//                            'campaign_field_id' => $campaignField['id']
//                        ));
//                    }
//	        	}

                //Field list
	        	$campaignFields = $campaign->findTable_CampaignField();
                $fieldsList = array();
                foreach($campaignFields as $campaignField)
                {
                    $fieldsList[$campaignField['field_alias']] = $campaignField['id'];
                }

                if($debug != "go"){
					if(count($values) > count($fieldsList)){
						die((count($values) - count($fieldsList))." VARIABLES TO MANY");
					}

					if(count($values) < count($fieldsList)){
						die((count($fieldsList) - count($values))." VARIABLES MISSING");
					}
                }

				//Preparing fields
				$unknownFields = array();
				$toStore = array();
				foreach ($values as $key=>$value) {
					if(in_array($key, array_keys($fieldsList)))
					{
						$toStore[$fieldsList[$key]] = $value;
					}
					else{
						$unknownFields[] = $key;
					}
				}

                //RETOURS

                if($debug != "go"){
					if(count($unknownFields))
					{
						echo("UNKNOWN FIELDS : " . implode(', ', $unknownFields) . ".\n");
						die("NOT OK");
					}
                }

				//Search duplicate email
				$sql = "SELECT 1 FROM lead WHERE campaign_id=".$cid." AND lead_data LIKE '%".$email."%'";

				$count = 0;
				foreach($toStore as $key=>$value)
				{
					$values[] = $key;
					$values[] = $value;
				}

				$result = Table_Campaign::getDefaultAdapter()
								->query($sql)
								->fetchAll();
				if(count($result) > 0)
				{
					die('DUPLICATE');
				}

	        	$TCampaignPublisher = new Table_CampaignPublisher();
	        	$campaignPublisher = $TCampaignPublisher->fetchRow("campaign_id=$cid AND publisher_id=$pid");

        	 	if ($campaignPublisher->ninety_reached == 0 && ($campaignPublisher->leads / $campaignPublisher->volume >= 0.9)) {
        	 		$campaignPublisher->ninety_reached = 1;

		        	$m = new BC_HtmlMailer();
			    	$m->setSubject('Alerte Campagne - Coregistration');
			    	$m->addTo($campaignPublisher->findParentTable_Campaign()->findParentTable_User()->email);
			    	$m->setViewParam('firstname', $campaignPublisher->findParentTable_Campaign()->findParentTable_User()->firstname);
			    	$m->setViewParam('campaign', $campaign->name);
			    	$m->sendHtmlTemplate('ninety_percent.phtml');
		        }

                //Call client web service to verify if the lead exists in clients database
	        	if ($campaignPublisher->webservice_url != '') {

	        		// the http call doesn't work if the url lacks http:// in the string
	        		// adding http:// if it is mising
	        		$url = $campaignPublisher->webservice_url;

	        		if (strpos($url, "http://") === false) {
	        			$url = "http://".$url;
	        		}

	        		$client = new Zend_Http_Client();
		        	$client->setUri($url);

		        	$db = Zend_Db_Table::getDefaultAdapter();

		        	// getting the advertiser id
		        	$stmt = $db->query("SELECT a.id
		        			FROM advertiser a
		        			INNER JOIN campaign_advertiser ca
		        			ON ca.advertiser_id = a.id
		        			WHERE ca.id = ".$params['cid']);

		        	$advertiser = $stmt->fetch();
		        	$advertiserId = $advertiser['id'];

		        	$TCampaignAdvertiserField = new Table_CampaignAdvertiserField();
		        	$fieldsValuesArray = array();

			        foreach ($campaignFields as $campaignField) {

			        	// replacing the field names with the advertisers own field names
			        	$stmt = $db->query("SELECT caf.advertiser_field
						        			FROM campaign_advertiser_field caf
						        			INNER JOIN campaign_field cf
						        			ON cf.id = caf.field_id
						        			WHERE cf.field_alias = '".$campaignField['field_alias']."'
						        			AND cf.campaign_id = ".$params['cid']);

			        	$advertiser = $stmt->fetch();
			        	$advertiserField = $advertiser['advertiser_field'];
			        	$fieldIndex = array_search($campaignField['id'], $values);
			        	$client->setParameterGet($advertiserField, $values[$fieldIndex+1]);

			        	// used only for the client Travelbird
			        	$fieldsValuesArray[$advertiserField] = $values[$fieldIndex+1];

			        	if($advertiserField == "")
			        		die("ALL FIELDS NOT MATCHED FOR THIS CAMPAIGN");
		        	}

		        	$client->setMethod(Zend_Http_Client::GET);

		        	try{

						/* Special solution only for the webservice of the client Travelbird */
		        		if(strpos($campaignPublisher->webservice_url, "travelbird")){
							$clientResponse = $this->travelBirdAPICall($fieldsValuesArray);
						}else{
							$response = $client->request();
						}

						if($debug == "go"){
							$string = $client->getLastRequest();
							$string = substr($string, 4, strpos ($string, "HTTP/1.1\r\n") - 5);
							die($url.$string);
						}
		        	}
		        	catch(Exception $e){
		        		die("CLIENT WEB SERVICE NOT OK.");
		        	}

					/* Special solution only for the webservice of the client Travelbird */
					if(!strpos($campaignPublisher->webservice_url, "travelbird")){
                    	$clientResponse = trim($response->getBody());
					}

					// special solution for Assuricia.
					// They want to test their webservice
					if($cid == 51){
						$fhVerified = fopen("/var/www/tracking/public/verified.txt", "a") or die("can't open file");
						$text = $email."\t".$clientResponse."\n";
						fwrite($fhVerified, $text);
						fclose($fhVerified);
					}
					
					// this is a special solution made for the client Ancylia
					// their webservise didn't generate "OK" as response, but responded with codes
					// 80 and 81 means "OK"
					$errorCode = substr($clientResponse, 0, 2);

					if($errorCode == "80" || $errorCode == "81" || $errorCode == "42" || $errorCode == "43" || $errorCode == "OK"){
						$clientResponse = "OK";
					}else{
						die($clientResponse);
					}
	        	}
                
                //Création d'une ligne de lead
                $TLead = new Table_Lead();
                $leadId = $TLead->insert(array(
                   'campaign_id' => $cid,
                   'publisher_id' => $pid,
                   'record_date' => date('Y-m-d H:i:s'),
                   'lead_data' => json_encode($values),
                ));


				//Storing fields
	        	$TLeadData = new Table_LeadData();
                foreach($toStore as $key=>$value)
                {
                    //Storing fields
                    $TLeadData->insert(array(
                        'lead_id' => $leadId,
                        'value' => $value,
                        'record_date' => date('Y-m-d H:i:s'),
                        'campaign_field_id' => $key,
                    ));
                }

	        	$campaignPublisher->leads += 1;
        		$campaignPublisher->save();

				$campaign = $this->getUnitEmailInfo($cid);
				if($campaign["unit_email_address"] != "")
					$this->sendUnitEmail($cid, $campaign, $valuesForUnitMail);
				
				die("OK");

        	} else {
        		die ("Impossible de retrouver la campagne !");
        	}
        } else {
        	die ("Variables manquantes dans l'appel");
        }
    }

	/*
	This function is created to call the TravelBird API since it is different from other customers API's
	It accepts only JSON encoded data that must be sent with POST
	*/
	private function travelBirdAPICall($values) {

		//$data = array('email'=>'user@testmail.nl','tr_referral'=>'BaseandCo','mailing_lists'=>4,'ip_address'=>'192.168.1.23');

		// adding manadatory fields for the Travelbird API
		$values['tr_referral'] = 'BaseandCo';
		$values['mailing_lists'] = 4;
		//$values['ip_address'] = '192.168.1.23';

		/* Encoding data array into JSON format */
		$data_string = json_encode($values);

		/* Initializing cURL session and setting up URL with login credentials */
		$ch = curl_init('http://api.travelbird.nl/v1/account_lead/?username=BaseandCo&api_key=24937651db8eee60733b8eb75d7a8a5037314678');

		/* Setting options for the cURL transfer */
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
													'Content-Type: application/json',
													'Content-Length: ' . strlen($data_string))
		);
		/* Executing the query API */
		$result = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		/* Now retrieving the data from the query result */
		if($code == "201")
			return "OK";
		else
			return $code." - ".$result;
	}
	
	private function getUnitEmailInfo($cid){
		$db = Zend_Db_Table::getDefaultAdapter();
		$ca = $db->query("SELECT ca.unit_email_address, ca.unit_email_subject, ca.unit_email_operation
							FROM campaign c
							INNER JOIN campaign_advertiser ca
							ON ca.id = c.campaign_advertiser_id
							WHERE c.id = ".$cid);

		$campaign = $ca->fetch();
		
		return $campaign;
	}

	private function sendUnitEmail($cid, $campaign, $values){

		$message = "";
		
		// START Special function developed only for EDF ENR (campaign id 39)
		if($cid == 39){
			$message =  array();
			
			foreach ($values as $key => $value){
				
				if($key == "firstname")
					$message[0] = "Prénom : " . $value."\r\n";

				if($key == "lastname")
					$message[1] = "Nom : " . $value."\r\n";

				if($key == "zipcode")
					$message[3] = "Code postal : " . $value."\r\n";

				if($key == "city")
					$message[4] = "Ville : " . $value."\r\n";

				if($key == "phone")
					$message[5] = "Téléphone : " . $value."\r\n";

				if($key == "email")
					$message[8] = "Email : " . $value."\r\n";				
			}
			
			$message[2] = "Adresse : \r\n";
			$message[6] = "Numéro de téléphone : \r\n";
			$message[7] = "Operation : " . $campaign["unit_email_operation"]."\r\n";

			ksort($message);
			$message = implode("", $message);
		// END Special function developed only for EDF ENR (campaign id 39)			
		}else{
			foreach ($values as $key => $value){
				// Skapa en funktion som tar bort "onödig" data i arrayen 
				$message .= $key . ' : ' . $value."\r\n";
			}
		
			$message .= "Operation : ".$campaign["unit_email_operation"];
		}
		
		$mail = new Zend_Mail('utf-8');
		$mail->setBodyText($message);
		$mail->setFrom('dontreply@baseandco.com');
		$mail->addTo($campaign['unit_email_address']);
		$mail->setSubject($campaign['unit_email_subject']);
		$mail->send();		
	}
}
