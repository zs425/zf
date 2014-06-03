<?php
class Admin_AsyncController extends Zend_Controller_Action
{
	public function init() {
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout()->disableLayout();
	}
	
	public function setwebserviceAction() {
		$cid = $_POST['cid'];
		$pid = $_POST['pid'];
		$campaignPublisherTable = new Table_CampaignPublisher();
		$campaignPublisher = $campaignPublisherTable->find($cid, $pid)->current();
		if ($campaignPublisher) {
			switch ($_POST['action']) {
				case 'off':
				$campaignPublisher->status = -1;
				break;
				case 'on':
				case 'active':
				$campaignPublisher->status = 1;
				break;
				case 'archive':
				$campaignPublisher->status = 0;
				break;
				
				default:
					;
				break;
			}
		}
		try {
			$campaignPublisher->save();
			$message = "ok";
		} catch (Exception $e) {
			$message = "no";
		}
		echo json_encode(array("message" => $message, 'passkey' => md5($cid . '&' . $pid)));
	}
	
	public function addadvertiserAction() {
		if ($this->getRequest()->isXmlHttpRequest()) {
			if (isset($_POST['name'])) {
				unset($_POST['PHPSESSID']);
				$advertiserValues = $_POST;
				$advertiserValues['creation_date'] = date('y-m-d H:m:s');;
				$advertiserValues['lastupdate_date'] = date('y-m-d H:m:s');;
				$advertiserTable = new Table_Advertiser();
				try {
					$advertiser_id = $advertiserTable->insert($advertiserValues);
					$values = array('code' => 'OK', 'id' => $advertiser_id);
					echo Zend_Json::encode($values);
				} catch (Zend_Db_Exception $e) {
					echo $e->getMessage();
				}
			} else {
				echo "ERROR";
			}
		}
	}
	
	public function addfieldAction() {
		if ($this->getRequest()->isXmlHttpRequest()) {
			if (isset($_POST['name'])) {
				$fieldValues = $_POST;
				$TField = new Table_Field();
				try {
					$field_alias = strtolower($this->stripAccents($fieldValues['name']));
					$TField->insert(array('alias' => $field_alias, 'name' => $fieldValues['name']));
					$values = array('code' => 'OK', 'alias' => $field_alias, 'name' => $fieldValues['name']);
					echo Zend_Json::encode($values);
				} catch (Zend_Db_Exception $e) {
					echo $e->getMessage();
				}
			} else {
				echo "ERROR";
			}
		}
	}
	
	private function stripAccents($text) {
		$text = str_replace(
			array(
				'à', 'â', 'ä', 'á', 'ã', 'å',
				'î', 'ï', 'ì', 'í', 
				'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 
				'ù', 'û', 'ü', 'ú', 
				'é', 'è', 'ê', 'ë', 
				'ç', 'ÿ', 'ñ',
				'À', 'Â', 'Ä', 'Á', 'Ã', 'Å',
				'Î', 'Ï', 'Ì', 'Í', 
				'Ô', 'Ö', 'Ò', 'Ó', 'Õ', 'Ø', 
				'Ù', 'Û', 'Ü', 'Ú', 
				'É', 'È', 'Ê', 'Ë', 
				'Ç', 'Ÿ', 'Ñ', ' '
			),
			array(
				'a', 'a', 'a', 'a', 'a', 'a', 
				'i', 'i', 'i', 'i', 
				'o', 'o', 'o', 'o', 'o', 'o', 
				'u', 'u', 'u', 'u', 
				'e', 'e', 'e', 'e', 
				'c', 'y', 'n', 
				'A', 'A', 'A', 'A', 'A', 'A', 
				'I', 'I', 'I', 'I', 
				'O', 'O', 'O', 'O', 'O', 'O', 
				'U', 'U', 'U', 'U', 
				'E', 'E', 'E', 'E', 
				'C', 'Y', 'N', '_'
			),$text);
		return $text;
	}
}
