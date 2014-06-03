<?php
class Form_Webservice extends ZendX_JQuery_Form
{
	public function init() {
		 $decorators = array(
			'ViewHelper',
			'Label',
			array('HtmlTag', array('tag' => 'p'))
		);
		
		$TCampaigns = new Table_Campaign();
		$rsCampaigns = $TCampaigns->fetchAll();
		$campaigns = array();
		
		foreach ($rsCampaigns as $campaign) {
			$campaigns[$campaign->id] = $campaign->name;
		}
		 
		$TPublishers = new Table_Publisher();
		$rsPublishers = $TPublishers->getCoregistered();
		$publishers = array();
	
		foreach ($rsPublishers as $publisher) {
			$publishers[$publisher->ID] = $publisher->Nom_Base;
		}
        //Sorting by name
        asort($publishers);
		
		$campaign_id = new Zend_Form_Element_Select('campaign_id');
		$campaign_id->setLabel('Campagne :')
		            ->setRequired(true)
		            ->setMultiOptions($campaigns)
		            ->setDecorators($decorators);		         
		        
		$publisher_id = new Zend_Form_Element_Select('publisher_id');
		$publisher_id->setLabel('Editeur :')
		             ->setRequired(true)
		             ->setMultiOptions($publishers)
		             ->setDecorators($decorators);
		      
		/*$price = new Zend_Form_Element_Text('price');
		$price->setLabel('Tarif :')
		      ->setRequired(false)
		      ->setDecorators($decorators);
		      
		$volume = new Zend_Form_Element_Text('volume');
		$volume->setLabel('Volume :')
		       ->setRequired(false)
		       ->setDecorators($decorators);*/

		$price = new ZendX_JQuery_Form_Element_Spinner(
			'price', 
			array(
				'jQueryParams' => array(
					'min' => 0.01, 
					'max' => 100, 
					'start' => '1',
					'suffix' => '€',
					'group' => ',',
					'step' => 0.01,
					'largeStep' => 1/*,
					'showOn' => 'both'*/
				),
				'label' => 'Tarif :', 
				'attribs' => array('class' => 'integers'),
				'decorators' => array(
					'UiWidgetElement',
					'Label',
					array('HtmlTag', array('tag' => 'p'))
				)
			)
		);
		$price->setValue(0);

		$volume = new ZendX_JQuery_Form_Element_Spinner(
			'volume', 
			array(
				'jQueryParams' => array(
					'min' => 1, 
					'max' => 200000, 
					'start' => 1,
					'increment' => 'fast',
					'group' => '',
					'step' => 100,
					'largeStep' => 1000
				),
				'label' => 'Volume :', 
				'attribs' => array('class' => 'integers'),
				'decorators' => array(
					'UiWidgetElement',
					'Label',
					array('HtmlTag', array('tag' => 'p'))
				)
			)
		);
		$volume->setValue(10);

		$webservice_url = new Zend_Form_Element_Text('webservice_url');
		$webservice_url->setLabel('Webservice client :')
		               ->setRequired(false)
		               ->setAttrib('placeholder', 'url du webservice client')
		               ->setDecorators($decorators);

		$email = new Zend_Form_Element_Email('email');
		$email->setLabel('Email contact :')
		               ->setRequired(false)
		               ->setDecorators($decorators);

		$this->addElements(array($campaign_id, $publisher_id, $price, $volume, $webservice_url, $email));

		$fieldMatchingSubForm = new Zend_Form_SubForm();
		$fieldMatchingSubForm->setLegend('Associer les champs de la campagne avec celles du client');
		$fieldMatchingSubForm->setDecorators(
				array(
						'FormElements',
						'Fieldset',
						array('HtmlTag', array('tag' => 'div'))
				)
		);
		
		$cid = Zend_Controller_Front::getInstance()->getRequest()->getParam( 'c', null );
		$pid = Zend_Controller_Front::getInstance()->getRequest()->getParam( 'p', null );
		
		// hide the subform when a new webservice is created
		if($cid){
			$TCampaign = new Table_Campaign();
			$campaign = $TCampaign->find($cid)->current();
			
			$campaignFields = $campaign->findTable_CampaignField();
			$fieldsList = array();
			
			foreach($campaignFields as $campaignField)
			{
				$fieldName = new Zend_Form_Element_Text($campaignField['id']);
				$fieldName->setLabel($campaignField['field_alias'])
				->addValidator(new Zend_Validate_StringLength(array('max' => '80')))
				->setDecorators($decorators);
				$fieldMatchingSubForm->addElement($fieldName);
			}
			$this->addSubForm($fieldMatchingSubForm, 'fieldMatchingForm');
		}
		
		$this->addElement('submit', 'submit', array('label' => 'Valider', 'style' => 'margin-left: 155px;', 'decorators' => array('ViewHelper', array('HtmlTag', array('tag' => 'p')))));
		
		$this->setDecorators(array(
			'FormElements',
			'Form'
		));
	}
}
