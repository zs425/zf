<?php
class Form_Campaign extends ZendX_JQuery_Form
{
	public function init() {
		$decorators = array(
			'ViewHelper',
			'Label',
			array('HtmlTag', array('tag' => 'p')),
			'Errors'
		);

		$ddl_validator = new Zend_Validate_GreaterThan(0);
		$ddl_validator->setMessage("Veuillez faire un choix.");

		$notEmpty_validator = new Zend_Validate_NotEmpty();
		$notEmpty_validator->setMessage('La valeur ne peut pas être vide');

		$campaignSubForm = new Zend_Form_SubForm();
		$campaignSubForm->setLegend('Informations');
		$campaignSubForm->setDecorators(
			array(
				'FormElements',
				'Fieldset',
				array('HtmlTag', array('tag' => 'div'))
			)
		);

		$status = new Zend_Form_Element_Radio('status');
		$status->setLabel('Statut :')
		       ->setRequired(true)
		       ->setMultiOptions(array('1' => 'Active', '0' => 'Archivée'))
		       ->setDecorators(array(
		       		'ViewHelper',
					'Label',
					array('HtmlTag', array('tag' => 'p')),
					'Errors'
		       ))
		       ->setAttrib('label_style', 'display: inline;')
		       ->setSeparator('');

		$type_coreg = new Zend_Form_Element_Radio('type_coreg');
		$type_coreg->setLabel('Type :')
		       ->setRequired(true)
		       ->setMultiOptions(array('1' => 'Coreg', '0' => 'Email'))
		       ->setDecorators(array(
		       		'ViewHelper',
					'Label',
					array('HtmlTag', array('tag' => 'p')),
					'Errors'
		       ))
		       ->setAttrib('label_style', 'display: inline;')
		       ->setSeparator('');
			   
		$duplicate_option = new Zend_Form_Element_Radio('duplicate_option');
		$duplicate_option->setLabel('Type de deduplication :')
		       ->setRequired(true)
		       ->setMultiOptions(array('0' => 'Email', '1' => 'Email + numéro de téléphone'))
		       ->setDecorators(array(
		       		'ViewHelper',
					'Label',
					array('HtmlTag', array('tag' => 'p')),
					'Errors'
		       ))
		       ->setAttrib('label_style', 'display: inline;')
		       ->setSeparator('');					   
			   
		$name = new Zend_Form_Element_Text('name');
		$name->setLabel('Nom de la campagne :')
		     ->setRequired(true)
		     ->setDecorators($decorators)
		     ->addFilter(new BC_Filter_StripSlashes())
		     ->addValidator($notEmpty_validator)
		     ->addValidator(new Zend_Validate_StringLength(array('max' => '80')));

		$start_date = new ZendX_JQuery_Form_Element_DatePicker(
			'start_date',
			array(
				'jQueryParams' => array(
					'dateFormat' => 'dd/mm/yy',
					'changeMonth' => 'true',
					'changeYear' => 'true',
					'maxDate' => '1Y',
					'monthNamesShort' => array('Jan','Fev','Mar','Avr','Mai','Jun','Jul','Août','Sep','Oct','Nov','Déc'),
					'monthNames' => array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'),
					'dayNames' => array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche')
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
		$start_date->setRequired(true)
		           ->addValidator($notEmpty_validator);

		$end_date = new ZendX_JQuery_Form_Element_DatePicker(
			'end_date',
			array(
				'jQueryParams' => array(
					'dateFormat' => 'dd/mm/yy',
					'changeMonth' => 'true',
					'changeYear' => 'true',
					'maxDate' => '1Y',
					'monthNamesShort' => array('Jan','Fev','Mar','Avr','Mai','Jun','Jul','Août','Sep','Oct','Nov','Déc'),
					'monthNames' => array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'),
					'dayNames' => array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche')
				),
				'label' => 'Date fin :',
				'decorators' => array(
					'UiWidgetElement',
					'Label',
					array('HtmlTag', array('tag' => 'p'))
				)
			)
		);
		$end_date->setRequired(false);

		$TUsers = new Table_User();
		$rsUsers = $TUsers->fetchAll();
		$users = array('0' => 'Choisissez...');

		foreach ($rsUsers as $user) {
                    if($user->status == 1)
                    {
			$users[$user->id] = $user->firstname . " " . $user->lastname;
                    }
		}

		$user_id = new Zend_Form_Element_Select('user_id');
		$user_id->setLabel('Chef de projet :')
		        ->setRequired(true)
		        ->setMultiOptions($users)
		        ->addValidator(new Zend_Validate_Int())
		        ->addValidator($ddl_validator)
		        ->setDecorators($decorators);

		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel('Description :')
		            ->setRequired(false)
		            ->setDecorators(array(
			'ViewHelper',
			array('Label', array('class' => 'ta')),
			array('HtmlTag', array('tag' => 'p'))
		));

		$TTargets = new Table_Target();
		$rsTargets = $TTargets->fetchAll();
		$targets = array('0' => 'Choisissez...');

		foreach ($rsTargets as $target) {
			$targets[$target->id] = $target->name;
		}

		/*$target_id = new Zend_Form_Element_Select('target_id');
		$target_id->setLabel('Cible :')
		          ->setRequired(false)
		          ->setMultiOptions($targets)
		          ->addValidator(new Zend_Validate_Int())
		          ->addValidator(new Zend_Validate_GreaterThan(0))
		          ->setDecorators($decorators);*/

		$campaignSubForm->addElements(
			array(
				$status,
				$type_coreg,
				$duplicate_option,
				$name,
				$start_date,
				$end_date,
				$user_id,
				$description/*,
				$target_id*/
			)
		);


		$campaignAdvertiserSubForm = new Zend_Form_SubForm();
		$campaignAdvertiserSubForm->setLegend('Annonceur');
		$campaignAdvertiserSubForm->setDecorators(
			array(
				'FormElements',
				'Fieldset',
				array('HtmlTag', array('tag' => 'div'))
			)
		);

		$TAdvertisers = new Table_Advertiser();
		$rsAdvertisers = $TAdvertisers->fetchAll();
		$advertisers = array('0' => 'Choisissez...');

		foreach ($rsAdvertisers as $advertiser) {
			$advertisers[$advertiser->id] = $advertiser->name;
		};

		$advertiser_id = new Zend_Form_Element_Select('advertiser_id');
		$advertiser_id->setLabel('Annonceur :')
		              ->setRequired(true)
		              ->setMultiOptions($advertisers)
		              ->setDescription('<a href="#" id="addAdvertiser">Ajouter</a>')//<img src="/images/add_16.png" width="16" height="16" alt="Ajouter" style="padding-top: 3px;"/>
		              ->addValidator(new Zend_Validate_Int())
		              ->addValidator($ddl_validator)
		              ->setDecorators(array(
		            	'ViewHelper',
				        'Label',
				        array('Description', array('escape' => false, 'tag' => false)),
				        array('HtmlTag', array('tag' => 'p')),
				        'Errors'
		            ));

		/*$rate = new Zend_Form_Element_Text('rate');
		$rate->setLabel('Tarif :')
		     ->setRequired(true)
		     ->addValidator(new Zend_Validate_Int())
		     ->setDecorators($decorators);*/

		$rate = new ZendX_JQuery_Form_Element_Spinner(
			'rate',
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
		$rate->setValue(0);

		$webservice_url = new Zend_Form_Element_Text('webservice_url');
		$webservice_url->setLabel('Webservice :')
		               ->setRequired(false)
		               ->setDecorators($decorators);

		$civilites["M."] = "M.";
		$civilites["Mme."] = "Mme.";
		$civilites["Mlle."] = "Mlle.";
		$civilite = new Zend_Form_Element_Select('title');
		$civilite->setLabel('Civilité :')
		              ->setRequired(true)
		              ->setMultiOptions($civilites)
		              ->setDecorators(array(
		            	'ViewHelper',
				        'Label',
				        array('Description', array('escape' => false, 'tag' => false)),
				        array('HtmlTag', array('tag' => 'p')),
				        'Errors'
		            ));

		$prenom = new Zend_Form_Element_Text('firstname');
		$prenom->setLabel('Prenom :')
                    ->setRequired(true)
		            ->setDecorators($decorators);

		$nom = new Zend_Form_Element_Text('lastname');
		$nom->setLabel('Nom :')
                    ->setRequired(true)
		            ->setDecorators($decorators);

		$email = new Zend_Form_Element_Text('email');
		$email->setLabel('Email (primaire) :')
		               ->setRequired(true)
                       ->addValidator('EmailAddress')
		               ->setDecorators($decorators);

		$email2 = new Zend_Form_Element_Text('email_secondary');
		$email2->setLabel('Email (secondaire) :')
		               ->setRequired(false)
                       ->addValidator('EmailAddress')
		               ->setDecorators($decorators);

		$email3 = new Zend_Form_Element_Text('email_third');
		$email3->setLabel('Email (troisième) :')
		               ->setRequired(false)
                       ->addValidator('EmailAddress')
		               ->setDecorators($decorators);

		$send_active = new Zend_Form_Element_Radio('send_active');
		$send_active->setLabel('Envoi active :')
				->addMultiOptions(array(
				'0' => 'Non',
				'1' => 'Oui'
				))
				->setValue(0)
				->setSeparator('')
				->setRequired(true)
				->setAttrib('label_style', 'display: inline;')
				->setDecorators(array(
					 'ViewHelper',
						 'Label',
						 array('HtmlTag', array('tag' => 'p')),
						 'Errors'
				));

		$send_transp = new Zend_Form_Element_Radio('send_transp');
		$send_transp->setLabel('Envoi transperent :')
				->addMultiOptions(array(
				'0' => 'Non',
				'2' => 'Non, avec id',
				'1' => 'Oui'
				))
				->setValue(0)
				->setSeparator('')
				->setRequired(true)
				->setAttrib('label_style', 'display: inline;')
				->setDecorators(array(
					 'ViewHelper',
						 'Label',
						 array('HtmlTag', array('tag' => 'p')),
						 'Errors'
				));

		$send_time = new ZendX_JQuery_Form_Element_Spinner('send_time',
			array(
				'jQueryParams' => array(
					'min' => 0,
					'max' => 23,
					'start' => '9',
					'suffix' => 'h',
					'group' => ',',
					'step' => 1,
					'largeStep' => 1/*,
					'showOn' => 'both'*/
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
		$send_time->setValue(0);

		$send_day = new Zend_Form_Element_MultiCheckbox('send_day');
		$send_day->setLabel('Le(s) jour(s) :')
				->setRegisterInArrayValidator(false)
				->addMultiOptions(array(
				'1' => 'Lu',
				'2' => 'Ma',
				'3' => 'Me',
				'4' => 'Je',
				'5' => 'Ve',
				'6' => 'Sa',
				'7' => 'Di',
				))
				->setSeparator('')
				->setRequired(true)
				->setAttrib('label_style', 'display: inline;')
				->setDecorators(array(
					 'ViewHelper',
						 'Label',
						 array('HtmlTag', array('tag' => 'p')),
						 'Errors'
				));

		$unit_email_address = new Zend_Form_Element_Text('unit_email_address');
		$unit_email_address->setLabel('Email unitaire - Email :')
                    ->setRequired(false)
					->addValidator('EmailAddress')
		            ->setDecorators(array(
									'ViewHelper',
									'Label',
									array('HtmlTag', array('tag' => 'p', 'class' => 'unitMailTopSpace')),
									'Errors'
									));

		$unit_email_subject = new Zend_Form_Element_Text('unit_email_subject');
		$unit_email_subject->setLabel('Email unitaire - Objet :')
                    ->setRequired(false)
		            ->setDecorators($decorators);

		$unit_email_operation = new Zend_Form_Element_Text('unit_email_operation');
		$unit_email_operation->setLabel('Email unit.- Operation :')
                    ->setRequired(false)
		            ->setDecorators($decorators);
					

		$ftp_host = new Zend_Form_Element_Text('ftp_host');
		$ftp_host->setLabel('FTP - adresse :')
                    ->setRequired(false)
		            ->setDecorators(array(
									'ViewHelper',
									'Label',
									array('HtmlTag', array('tag' => 'p')),
									'Errors'
									));

		$ftp_types["FTP"] = "FTP";
		$ftp_types["SFTP"] = "SFTP";
		$ftp_type = new Zend_Form_Element_Select('ftp_type');
		$ftp_type->setLabel('FTP - Type :')
		              ->setRequired(true)
		              ->setMultiOptions($ftp_types)
		              ->setDecorators(array(
		            	'ViewHelper',
				        'Label',
				        array('HtmlTag', array('tag' => 'p', 'class' => 'unitMailTopSpace')),
				        'Errors'
		            ));								

		$ftp_port = new Zend_Form_Element_Text('ftp_port');
		$ftp_port->setLabel('FTP - Port :')
                    ->setRequired(false)
					->setValue('21')
		            ->setDecorators($decorators);	
									
		$ftp_path = new Zend_Form_Element_Text('ftp_path');
		$ftp_path->setLabel('FTP - Répertoire :')
                    ->setRequired(false)
		            ->setDecorators($decorators);	

		$ftp_user = new Zend_Form_Element_Text('ftp_user');
		$ftp_user->setLabel('FTP - Utilisateur :')
                    ->setRequired(false)
		            ->setDecorators($decorators);
					
		//$ftp_password = new Zend_Form_Element_Password('ftp_password');
		$ftp_password = new Zend_Form_Element_Text('ftp_password');
		$ftp_password->setLabel('FTP - Mot de passe :')
                    ->setRequired(false)
		            ->setDecorators($decorators);
					//->setRenderPassword(true);

		$ftp_button = new Zend_Form_Element_Button('ftp_button');
		$ftp_button->setLabel('Tester la connexion FTP')
                    ->setRequired(false)
					->setAttrib('onClick','testFtp()')
		            ->setDecorators($decorators);
					
		$campaignAdvertiserSubForm->addElements(
			array(
				$advertiser_id,
				$rate,
				$webservice_url,
				$civilite,
				$prenom,
				$nom,
				$email,
				$email2,
				$email3,
				$civilite,
				$send_day,
				$send_time,
				$send_active,
				$send_transp,
				$unit_email_address,
				$unit_email_subject,
				$unit_email_operation,
				$ftp_type,				
				$ftp_host,
				$ftp_port,
				$ftp_path,
				$ftp_user,
				$ftp_password,
				$ftp_button
			)
		);

		$campaignFieldsSubForm = new Zend_Form_SubForm();
		$campaignFieldsSubForm->setLegend('Critères à récupérer');
		$campaignFieldsSubForm->setDecorators(
			array(
				'FormElements',
				'Fieldset',
				array('HtmlTag', array('tag' => 'div'))
			)
		);

		$TFields = new Table_Field();
		$rsFields = $TFields->fetchAll(null, 'name ASC');
		$fields = array();
		foreach ($rsFields as $field) {
			$element = new Zend_Form_Element_Text(/*'check_' . */$field->alias);
			$element->setLabel($field->name)
			        ->setDecorators(array(
						'ViewHelper',
						array('Label', array('placement' => 'append', 'class' => 'labelleft')),
						array('HtmlTag', array('tag' => 'p'))
					)
			);
			$campaignFieldsSubForm->addElement($element);
		}
		
		$campaignUnitEmailSubForm = new Zend_Form_SubForm();
		$campaignUnitEmailSubForm->setLegend('Email unitaire');
		$campaignUnitEmailSubForm->setDecorators(
			array(
				'FormElements',
				'Fieldset',
				array('HtmlTag', array('tag' => 'div'))
			)
		);

		$this->addSubForm($campaignSubForm, 'campaignForm');
		$this->addSubForm($campaignAdvertiserSubForm, 'campaignAdvertiserForm');
		$this->addSubForm($campaignFieldsSubForm, 'campaignFieldsForm');

		$this->addElement('submit', 'submit', array('label' => 'Valider'/*, 'style' => 'margin-left: 155px;'*/, 'decorators' => array('ViewHelper', array('HtmlTag', array('tag' => 'p')))));
		
		$this->setDecorators(array(
			'FormElements',
			'Form'
		));
	}
}
