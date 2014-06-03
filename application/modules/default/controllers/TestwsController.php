<?php

class TestwsController extends Zend_Controller_Action
{
	public function indexAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout()->disableLayout();
    	$params = $this->getRequest()->getParams();

		if(isset($params['cid'])){
			$filename = "";
			$url = "http://www.b-home.fr/WebService/service_bco.php"; // till kundens webservice

			$lines = file("/var/www/tracking/public/exported.txt");
			$fhVerified = fopen("/var/www/tracking/public/verified.txt", 'w') or die("can't open file");

			/*$client = new Zend_Http_Client();
			$client->setUri($url);
			$client->setMethod(Zend_Http_Client::GET);
			$client->setParameterGet("CIVILITE_CONTACT", "M"); // in med variablerna här
			$client->setParameterGet("NOM_CONTACT", "Lastname"); // in med variablerna här
			$client->setParameterGet("PRENOM_CONTACT", "Firstname"); // in med variablerna här
			$client->setParameterGet("ADR1", "6 rue Paris"); // in med variablerna här
			$client->setParameterGet("CP", "75001"); // in med variablerna här
			$client->setParameterGet("VILLE", "Paris"); // in med variablerna här
			$client->setParameterGet("DATE_NAISSANCE_CONTACT", "010190"); // in med variablerna här
			$client->setParameterGet("SOURCE", "Website source"); // in med variablerna här
			$client->setParameterGet("TELEPHONE", "0123456789"); // in med variablerna här*/

            foreach ($lines as $email) {
                /*$client->setParameterGet("MAIL", $email); // in med variablerna här
				$response = $client->request();
                $response = trim($response->getBody());*/

                $url = "http://www.b-home.fr/WebService/service_bco.php?CIVILITE_CONTACT=M&NOM_CONTACT=Name&PRENOM_CONTACT=Firstname&ADR1=My address 6&CP=75001&VILLE=Paris&DATE_NAISSANCE_CONTACT=01011990&SOURCE=websitesource&TELEPHONE=0123456789&MAIL=".$email;
                //$response = file_get_contents($url);
                echo "<html><head></head><body style='font-size:9px;font-family:arial;'>";
                echo "<a href='".$url."' target='_blank'>".$url."</a><br>";
                echo "</body></html>";
                //echo $url;
                //$text = trim($email)."\t".$response."\n";
                //fwrite($fhVerified, $text);
                //usleep($seconds*3000000);
			}
			fclose($fhVerified);
		}
	}
}

?>
