<?php
class Form_Publisher extends ZendX_JQuery_Form
{
	public function init() {
		$decorators = array(
			'ViewHelper',
			'Label',
			array('HtmlTag', array('tag' => 'p')),
			'Errors'
		);
		
		$publisherSubForm = new Zend_Form_SubForm();
		$publisherSubForm->setLegend('Informations');
		$publisherSubForm->setDecorators(
			array(
				'FormElements',
				'Fieldset',
				array('HtmlTag', array('tag' => 'div'))
			)
		);
		
		$status = new Zend_Form_Element_Radio('status');
		$status->setLabel('Statut :')
		       ->setRequired(true)
		       ->setMultiOptions(array('1' => 'Actif', '0' => 'Archivé'))
		       ->setDecorators(array(
		       		'ViewHelper',
					'Label',
					array('HtmlTag', array('tag' => 'p')),
					'Errors'
		       ))
		       ->setAttrib('label_style', 'display: inline;')
		       ->setSeparator('');
		
		$name = new Zend_Form_Element_Text('name');
		$name->setLabel('Nom :')
		     ->setRequired(true)
		     ->setDecorators($decorators)
		     ->addValidator(new Zend_Validate_NotEmpty())
		     ->addValidator(new Zend_Validate_StringLength(array('max' => '80')));
		     
		$website = new Zend_Form_Element_Text('website');
		$website->setLabel('Site web :')
		        ->setRequired(true)
		        ->setDecorators($decorators)
		        ->addValidator(new Zend_Validate_NotEmpty());
		        		        
		$webservice_url = new Zend_Form_Element_Text('webservice_url');
		$webservice_url->setLabel('Webservice :')
		               ->setRequired(true)
		               ->setDecorators($decorators)
		               ->addValidator(new Zend_Validate_NotEmpty());

		$TCategory = new Table_Category();
		$rsCategory = $TCategory->fetchAll(null, 'name ASC');
		$categories = array();
		
		foreach ($rsCategory as $category) {
			$categories[$category->id] = $category->name;
		}
		               
		$category_id = new Zend_Form_Element_Select('category_id');
		$category_id->setLabel('Catégorie :')
		            ->setRequired(true)
		            ->setMultiOptions($categories)
		            ->setDescription('<a href="#">Ajouter</a>')
		            ->setDecorators(array(
		            	'ViewHelper',
				        'Label',
				        array('Description', array('escape' => false, 'tag' => false)),
				        array('HtmlTag', array('tag' => 'p')),
				        'Errors'
		            ));

		$comments = new Zend_Form_Element_Textarea('comments');
		$comments->setLabel('Commentaires :')
		         ->setRequired(false)
		         ->setDecorators(array(
						'ViewHelper',
						array('Label', array('class' => 'ta')),
						array('HtmlTag', array('tag' => 'p'))
					));
		
		$publisherSubForm->addElements(
			array(
				$status,
				$name,
				$website,
				//$webservice_url,
				$category_id,
				$comments
			)
		);
		
		$contactSubForm = new Zend_Form_SubForm();
		$contactSubForm->setLegend('Contact');
		$contactSubForm->setDecorators(
			array(
				'FormElements',
				'Fieldset',
				array('HtmlTag', array('tag' => 'div'))
			)
		);
		          
		$lastname = new Zend_Form_Element_Text('lastname');
		$lastname->setLabel('Nom :')
		         ->setRequired(false)
		         ->setDecorators($decorators);
		
		$firstname = new Zend_Form_Element_Text('firstname');
		$firstname->setLabel('Prénom :')
		          ->setRequired(false)
		          ->setDecorators($decorators);

		$email = new Zend_Form_Element_Email('email');          
		$email->setLabel('Email :')
		      ->setRequired(true)
		      ->addValidator(new Zend_Validate_EmailAddress())
		      ->setDecorators($decorators);
		      
		$phone = new Zend_Form_Element_Text('phone');
		$phone->setLabel('Téléphone :')
		      ->setRequired(false)
		      ->setDecorators($decorators)
		      ->setAttrib('size', '10');
		      
		$contactSubForm->addElements(
			array(
				$lastname,
				$firstname,
				$email,
				$phone
			)
		);
		
		$salesModelSubForm = new Zend_Form_SubForm();
		$salesModelSubForm->setLegend("Modèles d'achat"); 
		$salesModelSubForm->setDecorators(
			array(
				'FormElements',
				'Fieldset',
				array('HtmlTag', array('tag' => 'div'))
			)
		);
		
		$TSalesModels = new Table_SalesModel();
		$rsSalesModels = $TSalesModels->fetchAll();
		foreach ($rsSalesModels as $salesModel) {
			$element = new Zend_Form_Element_Checkbox($salesModel->alias);
			$element->setLabel($salesModel->name)
			        ->setDecorators(array(
						'ViewHelper',
						array('Label', array('placement' => 'append', 'class' => 'labelleft')),
						array('HtmlTag', array('tag' => 'p'))
					)
			);
			$salesModelSubForm->addElement($element);
		}
		
		$this->addSubForm($publisherSubForm, 'publisherForm');
		$this->addSubForm($contactSubForm, 'contactForm');		
		$this->addSubForm($salesModelSubForm, 'salesModelForm');
		
		$this->addElement('submit', 'submit', array('label' => 'Valider', 'style' => 'margin-left: 0;', 'decorators' => array('ViewHelper', array('HtmlTag', array('tag' => 'p')))));
		
		$this->setDecorators(array(
			'FormElements',
			'Form'
		));
	}
}