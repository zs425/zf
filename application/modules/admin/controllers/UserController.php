<?php
class Admin_UserController extends BC_Controller_Action
{
	/**
	 * Table User
	 * 
	 * @var Table_User
	 */
	private $_userTable;
	
	public function init() {
		$this->_userTable = new Table_User();
	}
	
	public function indexAction() {
		$this->_redirect($this->_helper->url('list', 'user', 'admin'));
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
				$this->view->setTitrePage("Utilisateurs archivés");
				$this->view->users = $this->_userTable->fetchAll('status = 0');
			}
		} else {
			$this->view->setTitrePage("Utilisateurs actifs");
			$this->view->users = $this->_userTable->fetchAll('status = 1');
		}
	}
	
    /**
     * Archives / Unarchives a user
     */
    public function archiveAction(){
        
		$params = $this->getRequest()->getParams();
        $userId = $params['u'];
        
        if($userId)
        {
            $user = $this->_userTable->find((int)$params['u'])->current();
            $user->status = $user->status == 1 ? '0':'1';
            $user->save();
        }
        
        $this->_redirect($this->_helper->url('list', 'user', 'admin'));
    }
    
	public function editAction() {
		$params = $this->getRequest()->getParams();
        $isUpdate = isset($params['u']);
        
        if ($isUpdate) {
            $params['u'] = (int)$params['u'];
            $this->view->setTitrePage("Editer un utilisateur");
            $user = $this->_userTable->find((int)$params['u'])->current();
            
        } else {
            $this->view->setTitrePage("Créer un utilisateur");
            $user = $this->_userTable->createRow();
            $user->status = '1';
        }

        $form = new Form_User();
        $form->setAction($this->view->link('user' , 'edit', 'admin', $isUpdate ? array('u' => $params['u']) : '', 'default', !$isUpdate))
             ->setMethod('post')
             ->setDefaults($user->toArray());

        //Removing password confirmation if creating
        if( ! $isUpdate){
        	$form->removeElement('passwordConfirmation');
        }
        
             
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
        	$userValues = $form->getValues();
        	
           	if (!$isUpdate) {
	            $userValues['registration_date'] = date('y-m-d H:m:s');
           	}

            //If a password has been provided
            $password = $userValues['password'];
            unset($userValues['password']);
            if($password || !$isUpdate)
            {
                //If password==confirmation or we are creating
                if($password == $userValues['passwordConfirmation'] || !$isUpdate)
                {
                    $userValues['password'] = sha1($password);
                }
                //Else password error
                else
                {
                    $this->view->hasError = true;
                    $this->_helper->FlashMessenger->addMessage('Les mots de passe fournis ne correspondent pas, le mot de passe n\'a pas été modifié.');
                }
            }
           	
            $user->setFromArray(array_intersect_key($userValues, $user->toArray()));

            $campaignId = $user->save();
            
            $flashMessenger = $this->_helper->FlashMessenger;
		    $message = "L'utilisateur '" . $user->firstname . " " . $user->lastname . "' a bien été ";
		    if ($isUpdate) {
		    	$message .= "modifié.";
		    } else {
		    	$message .= "créé.";
		    }
		    $flashMessenger->addMessage($message);

            $this->_redirect($this->_helper->url('list', 'user', 'admin'));
        }
        
        $this->view->form = $form;
	}
	
}
