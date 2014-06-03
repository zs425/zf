<?php
class Admin_SetupController extends Zend_Controller_Action
{
	public function init() {
		//$this->_helper->aclCheck($this->getRequest()->getActionName(), 'edit');
	}
	public function indexAction() {
		$this->view->setTitrePage("Paramètres");
		
	}

	public function userAction() {
	}
}
