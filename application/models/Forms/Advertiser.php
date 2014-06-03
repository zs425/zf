<?php
class Form_Advertiser extends ZendX_JQuery_Form
{
	public function init() {
		$decorators = array(
			'ViewHelper',
			array('Label', array('class' => 'medium')),
			array('HtmlTag', array('tag' => 'p')),
			'Errors'
		);
		
		$status = new Zend_Form_Element_Radio('status');
		$status->setLabel('Statut :')
		       ->setRequired(true)
		       ->setMultiOptions(array('1' => 'Actif', '0' => 'Archivé'))
		       ->setDecorators(array(
		       		'ViewHelper',
					array('Label', array('class' => 'medium')),
					array('HtmlTag', array('tag' => 'p')),
					'Errors'
		       ))
		       ->setAttrib('label_style', 'display: inline;')
		       ->setSeparator('');
		       
		$name = new Zend_Form_Element_Text('name');
		$name->setLabel('Nom :')
		     ->setRequired(true)
		     ->setDecorators($decorators)
		     ->addFilter(new BC_Filter_StripSlashes())
		     ->addValidator(new Zend_Validate_NotEmpty())
		     ->addValidator(new Zend_Validate_StringLength(array('max' => '80')));
		     
		$website = new Zend_Form_Element_Text('website');
		$website->setLabel('Site web :')
		        ->setRequired(true)
		        ->setDecorators($decorators)
		        ->addValidator(new Zend_Validate_NotEmpty());
		
		$this->addElements(
			array(
				$status,
				$name,
				$website,
			)
		);
		        
		$this->setDecorators(array(
		    'FormElements',
		    'Form',
		    array('DialogContainer', array(
		        'id'          => 'advertiserContainer',
		        'jQueryParams' => array(
		    		'width' => '340px',
		            'tabPosition' => 'top',
		    		'autoOpen' => false,
		    		'title' => 'Ajouter un annonceur',
		    		'resizable' => false,
		    		'modal' => true/*,
		    		'buttons' => array(array('text' => 'Créer'), array('text' => 'Annuler'))*/
		        ),
		    )),
		));
		 
	}
}
