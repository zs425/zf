<?php
class Form_TestData extends ZendX_JQuery_Form
{
	public function init() {
		$decorators = array(
			'ViewHelper',
			'Label',
			array('HtmlTag', array('tag' => 'span')),
			'Errors'
		);
		
		$pwLengthValidator = new Zend_Validate_StringLength(array('min' => '2'));
		$pwLengthValidator->setMessage("Le champ doit contenir minimum 2 caractères.");
		
		$notEmpty_validator = new Zend_Validate_NotEmpty();
		$notEmpty_validator->setMessage('La valeur ne peut pas être vide');

		$testdata = new Zend_Form_Element_Text('data');
		$testdata->setLabel('Email ou téléphone :')
		          ->setRequired(true)
		          ->setDecorators($decorators)
		          ->addFilter(new BC_Filter_StripSlashes())
		     	  ->addValidators(array($pwLengthValidator, $notEmpty_validator));
		     	  		           
		$this->addElements(array(
			$testdata
		));
		
		$this->addElement('submit', 'submit', array('label' => 'Ajouter', 'style' => 'margin-left: 10px;margin-bottom: 10px;', 'decorators' => array('ViewHelper', array('HtmlTag', array('tag' => 'span')))));
		
		$this->setDecorators(array(
			'FormElements',
			'Form'
		));
	}
}
