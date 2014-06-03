<?php
class Form_User extends ZendX_JQuery_Form
{
	public function init() {
		$decorators = array(
			'ViewHelper',
			'Label',
			array('HtmlTag', array('tag' => 'p')),
			'Errors'
		);
		
		$pwLengthValidator = new Zend_Validate_StringLength(array('min' => '8'));
		$pwLengthValidator->setMessage("Le mot de passe doit contenir minimum 8 caractères.");
		
		$pwCharValidator = new Zend_Validate_Alnum();
		$pwCharValidator->setMessage("Le mot de passe ne peut contenir que des lettres et des chiffres.");
		
		$notEmpty_validator = new Zend_Validate_NotEmpty();
		$notEmpty_validator->setMessage('La valeur ne peut pas être vide');
		
		$status = new Zend_Form_Element_Radio('status');
		$status->setLabel('Statut :')
		       ->setRequired(true)
		       ->setMultiOptions(array('1' => 'Actif', '0' => 'Désactivé'))
		       ->setDecorators(array(
		       		'ViewHelper',
					'Label',
					array('HtmlTag', array('tag' => 'p')),
					'Errors'
		       ))
		       ->setAttrib('label_style', 'display: inline;')
		       ->setSeparator('');
		
		$firstname = new Zend_Form_Element_Text('firstname');
		$firstname->setLabel('Prénom :')
		          ->setRequired(false)
		          ->setDecorators($decorators)
		          ->addFilter(new BC_Filter_StripSlashes())
		     	  ->addValidator(new Zend_Validate_StringLength(array('max' => '80', 'min' => '2')));
		     	  
		     	  
		$lastname = new Zend_Form_Element_Text('lastname');
		$lastname->setLabel('Nom :')
		         ->setRequired(false)
		         ->setDecorators($decorators)
		         ->addFilter(new BC_Filter_StripSlashes())
		     	 ->addValidator(new Zend_Validate_StringLength(array('max' => '80', 'min' => '2')));
		
		$email = new Zend_Form_Element_Email('email');
		$email->setLabel('Email :')
		      ->setRequired(true)
		      ->setDecorators($decorators)
		      ->addFilter(new BC_Filter_StripSlashes())
		      ->addValidator(new Zend_Validate_StringLength(array('max' => '80')))
		      ->addValidator(new Zend_Validate_EmailAddress());
		      
		$password = new Zend_Form_Element_Password('password');
		$password->setLabel('Mot de passe :')
		         ->setRequired(true)
		         ->setDecorators($decorators)
		         ->addFilter(new BC_Filter_StripSlashes())
		         ->addValidators(array($pwLengthValidator, $pwCharValidator));
		$passwordConfirmation = new Zend_Form_Element_Password('passwordConfirmation');
		$passwordConfirmation->setLabel('Confirmation :')
		         ->setRequired(true)
		         ->setDecorators($decorators)
		         ->addFilter(new BC_Filter_StripSlashes())
		         ->addValidators(array($pwLengthValidator, $pwCharValidator));

		$phone = new Zend_Form_Element_Text('phone');
		$phone->setLabel('Téléphone :')
		      ->setRequired(false)
		      ->setDecorators($decorators)
		      ->addFilter(new BC_Filter_StripSlashes());
		         
		$cachedRoles = BC_Cache::get('roles');
		if (!$cachedRoles) {
			$TRoles = new Table_Role();
			$cachedRoles = $TRoles->fetchAll(null, 'rank ASC')->toArray();
			BC_Cache::set($cachedRoles, 'roles');
		}
		$roles = array();
		foreach ($cachedRoles as $role) {
			$roles[$role['alias']] = $role['name'];
		}
		         
		$role_alias = new  Zend_Form_Element_Select('role_alias');
		$role_alias->setLabel('Rôle : ')
		           ->setRequired(true)
		           ->setMultiOptions($roles)
		           ->addValidator(new Zend_Validate_NotEmpty())
		           ->setDecorators($decorators);
		           
		$this->addElements(array(
			$firstname,
			$lastname,
			$email,
			$password,
            $passwordConfirmation,
			$phone,
			$role_alias
		));
		
		$this->addElement('submit', 'submit', array('label' => 'Valider', 'style' => 'margin-left: 153px;', 'decorators' => array('ViewHelper', array('HtmlTag', array('tag' => 'p')))));
		
		$this->setDecorators(array(
			'FormElements',
			'Form'
		));
	}
}
