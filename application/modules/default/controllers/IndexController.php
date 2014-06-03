<?php
class IndexController extends Zend_Controller_Action
{
	public function indexAction() {
		$this->_redirect($this->_helper->url('list', 'index', 'admin'));
	}
	
	public function testAction() {
		die (md5('1&5'));
	}
}
