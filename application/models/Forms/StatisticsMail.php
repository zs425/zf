<?php
class Form_StatisticsMail extends ZendX_JQuery_Form
{
	public function init() {
		$decorators = array(
			'ViewHelper',
			'Label',
			array('HtmlTag', array('tag' => 'p')),
			'Errors'
		);

		$notEmpty_validator = new Zend_Validate_NotEmpty();
		$notEmpty_validator->setMessage('La date ne peut pas être vide');
        
		$TAdvertisers = new Table_Advertiser();
		$rsAdvertisers = $TAdvertisers->fetchAll();
		$advertisers = array('0' => 'Choisissez...');

		foreach ($rsAdvertisers as $advertiser) {
			$advertisers[$advertiser->id] = $advertiser->name;
		};

		$start_date = new ZendX_JQuery_Form_Element_DatePicker(
			'start_date',
			array(
				'jQueryParams' => array(
					'dateFormat' => 'dd/mm/yy'
				),
				'label' => 'Date début :',
				'decorators' => array(
					'UiWidgetElement',
					'Label',
					array('HtmlTag', array('tag' => 'p')),
					'Errors'
				)
			)
		);
		$start_date->setRequired(true);

		$end_date = new ZendX_JQuery_Form_Element_DatePicker(
			'end_date',
			array(
				'jQueryParams' => array(
					'dateFormat' => 'dd/mm/yy'
				),
				'label' => 'Date fin :',
				'decorators' => array(
					'UiWidgetElement',
					'Label',
					array('HtmlTag', array('tag' => 'p')),
					'Errors'
				)
			)
		);
		$end_date->setRequired(true);

		$send_time = new ZendX_JQuery_Form_Element_Spinner('send_time',
			array(
				'jQueryParams' => array(
					'min' => 0,
					'max' => 23,
					'suffix' => 'h',
					'group' => ',',
					'step' => 1,
					'largeStep' => 1
				),
				'label' => 'Envoi à :',
				'attribs' => array('class' => 'integers'),
				'decorators' => array(
					'UiWidgetElement',
					'Label',
					array('HtmlTag', array('tag' => 'p'))
				)
			)
		);
		$send_time->setValue(7);
        
		$end_time = new ZendX_JQuery_Form_Element_Spinner('end_time',
			array(
				'jQueryParams' => array(
					'min' => 0,
					'max' => 23,
					'suffix' => 'h',
					'group' => ',',
					'step' => 1,
					'largeStep' => 1
				),
				'label' => 'Envoi à :',
				'attribs' => array('class' => 'integers'),
				'decorators' => array(
					'UiWidgetElement',
					'Label',
					array('HtmlTag', array('tag' => 'p'))
				)
			)
		);
		$end_time->setValue(23);
				
		$this->addElements(
			array(
				$start_date,
                $end_date,
				$send_time,
			)
		);

		$this->addElement('submit', 'submit', array('label' => 'Envoyer', 'style' => 'margin-left: 155px;', 'decorators' => array('ViewHelper', array('HtmlTag', array('tag' => 'p')))));
		      
        $this->setDecorators(array(
			'FormElements',
			'Form'
		));
	}
}
