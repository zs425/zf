<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	public function _initBCMail() {
		$this->bootstrap('mail');
	}

	public function _initRoutes() {
		$this->bootstrap('router');
	}

	public function _initAutoload() {
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace('BC_');
		$autoloader->registerNamespace('JPGraph_');

		$moduleLoader = new Zend_Application_Module_Autoloader(
			array(
				'namespace' => '',
				'basePath' => APPLICATION_PATH
			)
		);

		return $moduleLoader;
	}

	public function _initLogs() {
        $this->bootstrap("log");
        $logger = $this->getResource("log");

		Zend_Registry::set("logger", $logger);
	}

	public function _initModels() {

		$platform = new Zend_Loader_Autoloader_Resource(
			array(
				'basePath' => APPLICATION_PATH . '/models',
				'namespace' => '',
				'resourceTypes' => array(
					'tables' => array('path' => 'Tables/', 'namespace' => 'Table'),
					'forms' => array('path' => 'Forms', 'namespace' => 'Form')
				)
			)
		);
	}

	public function _initDefines()
	{
		define('EXPORT_FOLDER', $this->getOption('export_folder'));
	}

}

