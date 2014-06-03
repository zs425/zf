<?php
class Admin_PublisherController extends BC_Controller_Action
{
	/**
	 * Table CampaignPublisher
	 * 
	 * @var Table_CampaignPublisher
	 */
	private $_publisherTable;
	
	/**
	 * Table Contact
	 * 
	 * @var Table_Contact
	 */
	private $_contactTable;
	
	/**
	 * Table PublisherSalesModel
	 * 
	 * @var Table_PublisherSalesModel
	 */
	private $_publisherSalesModelTable;
	
	public function init() {
		$this->_helper->redirector->gotoUrl('http://baseandco.com/intranet/ListeEditeurs/');
		$this->_publisherTable = new Table_Publisher();
		$this->_contactTable = new Table_Contact();
		$this->_publisherSalesModelTable = new Table_PublisherSalesModel();
	}
	
	public function indexAction() {
		$this->_redirect($this->_helper->url('list', 'publisher', 'admin'));
	}
	
	public function listAction() {
        
        $messages = $this->_helper->FlashMessenger->getMessages();
        
        if (!empty($messages)) {
	        $this->view->message = $messages[0];        	
        }
		$params = $this->getRequest()->getParams();
        $this->view->filter = false;
		if (isset($params['filter'])) {
			if ($params['filter'] == 'archived') {
				$this->view->filter = true;
				$this->view->setTitrePage("Editeurs archivés");
				$publishers = $this->_publisherTable->fetchAll('Statut = 0')->toArray();
			}
		} else {
			$this->view->setTitrePage("Editeurs actifs");
			$publishers = $this->_publisherTable->fetchAll('Statut = 1')->toArray();
		}
		$paginator = Zend_Paginator::factory($publishers);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $this->view->publishers = $paginator;
	}
	
	public function editAction() {
		$params = $this->getRequest()->getParams();
        $isUpdate = isset($params['p']);
        $salesModels = array();
        if ($isUpdate) {
            $params['p'] = (int)$params['p'];
            $this->view->setTitrePage("Editer un éditeur");
            $publisher = $this->_publisherTable->find((int)$params['p'])->current();
            $contact = $publisher->findParentTable_Contact();
            $salesModelsObjs = $publisher->findTable_PublisherSalesModel()->toArray();
            foreach ($salesModelsObjs as $k=>$v) {
            	$salesModels[$v['sales_model_alias']] = '1';
            }
            
        } else {
            $this->view->setTitrePage("Créer un éditeur");
            $publisher = $this->_publisherTable->createRow();
            $publisher->status = '1';
            $contact = $this->_contactTable->createRow();
        }

        $form = new Form_Publisher();
        $form->setAction($this->view->link('publisher' , 'edit', 'admin', $isUpdate ? array('p' => (int)$params['p']) : '', 'default', !$isUpdate))
             ->setMethod('post');
        $form->getSubForm('publisherForm')->setDefaults($publisher->toArray());     
		$form->getSubForm('contactForm')->setDefaults($contact->toArray());
		$form->getSubForm('salesModelForm')->setDefaults($salesModels);
             
        if ($this->getRequest()->isPost() && $form->isValid($_POST)) {
        	$publisherValues = $form->getSubForm('publisherForm')->getValues();
			$publisherValues = $publisherValues['publisherForm'];
        	// Contact
        	$contactValues = $form->getSubForm('contactForm')->getValues();
			$contactValues = $contactValues['contactForm'];
        	$contact->setFromArray(array_intersect_key($contactValues, $contact->toArray()));
        	
        	$publisherValues['contact_id'] = $contact->save();
        	if (!$isUpdate) {
				$publisherValues['creation_date'] = date('Y-m-d H:i:s');
			}
			$publisherValues['lastupdate_date'] = date('Y-m-d H:i:s');
			$publisher->setFromArray(array_intersect_key($publisherValues, $publisher->toArray()));
            $publisher_id = $publisher->save();
            
        	// Sales Model
        	$salesValues = $form->getSubForm('salesModelForm')->getValues();
        	$salesValues = $salesValues['salesModelForm'];
        	$this->_publisherSalesModelTable->delete('publisher_id = ' . $publisher_id);
        	foreach ($salesValues as $k=>$v) {
        		if ($v == '1') {
	        		$this->_publisherSalesModelTable->insert(array('publisher_id' => $publisher_id, 'sales_model_alias' => $k));
        		}
        	}
        	
        	    $flashMessenger = $this->_helper->FlashMessenger;
			    $message = "L'éditeur '" . $publisher->name . "' a bien été ";
			    if ($isUpdate) {
			    	$message .= "modifié.";
			    } else {
			    	$message .= "créé.";
			    }
			    $flashMessenger->addMessage($message);
        	
        	$this->_redirect($this->_helper->url(null, 'publisher', 'admin'));
        }
        
        $this->view->form = $form;
	}
	
	public function viewAction() {
		
	}
	
	public function archiveAction() {
		$params = $this->getRequest()->getParams();
        if (isset($params['p'])) {
        	$publisher = $this->_publisherTable->find($params['p'])->current();
        	if (isset($params['confirm'])) {
        		if ($params['confirm'] == 'do') {
        			$publisher->status = 0;
        			$publisher->save();
        			$this->_helper->FlashMessenger->addMessage("L'éditeur '" . $publisher->name . "' a bien été archivé.");
        			$this->_redirect($this->_helper->url(null, 'publisher', 'admin'));
        		}
        	} else {
        		$this->view->publisher = $publisher;
        	}
        } else {
        	$this->_redirect($this->_helper->url(null, 'publisher', 'admin'));
        }
	}
	
	public function statusAction() {
		
	}
}
