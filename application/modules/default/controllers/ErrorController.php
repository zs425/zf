<?php
class ErrorController extends Zend_Controller_Action
{
	/*
		Catching and treating the error.
		Can send mail if demanded.
	*/
	public function errorAction()
    {
		$error = $this->_getParam('error_handler');
        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Oups ! La page demandée est introuvable !';
                $this->view->stack_trace = $this->_getFullErrorMessage($error);
				$this->view->code = 404;
                break;

            default:
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Une erreur interne est survenue. Veuillez rafraichir la page.<br />Si le problème persiste, contactez l\'administrateur.';

				$this->_registerError($error, 1);

				$errorMsg = $this->_getFullErrorMessage($error);
				$this->view->stack_trace = $errorMsg;
				$this->view->code = 500;
                break;
        }

        $this->view->headTitle()->prepend( $this->view->code .  ' Error' );
    }

	/*
		Prepares and sends mail with the full error message.
	*/
	protected function _registerError($error, $sendMail = 0){

		$message  = "Error! \n";
		$message .= "Message: " . $error->exception->getMessage() . "\n";
		$message .= "Trace: " . $error->exception->getTraceAsString() . "\n";
		$message .= "Request data: " . var_export($error->request->getParams(), true) . "\n\n";

		if($sendMail == 1) {
			$mail = new Zend_Mail('utf-8');
			$mail->setBodyHtml($message);
			$mail->setFrom('zend_error_tracking@baseandco.com');
			$mail->addTo('sysadmin@baseandco.com');
			$mail->setSubject('Zend error on Tracking');
			$mail->send();
		}

		$logger = Zend_Registry::get('logger');
		$logger->log($message, 3);
	}

	/*
		Getting the full error message.
	*/
	protected function _getFullErrorMessage($error)
    {
		if (APPLICATION_ENV != 'development' && APPLICATION_ENV != 'staging')
		{
			return '';
		}

        $message = '';

        if (!empty($_SERVER['SERVER_ADDR'])) {
            $message .= "Server IP: " . $_SERVER['SERVER_ADDR'] . "\n";
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $message .= "User agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $message .= "Request type: " . $_SERVER['HTTP_X_REQUESTED_WITH'] . "\n";
        }

        $message .= "Server time: " . date("Y-m-d H:i:s") . "\n";
        $message .= "RequestURI: " . $error->request->getRequestUri() . "\n";

        if (!empty($_SERVER['HTTP_REFERER'])) {
            $message .= "Referer: " . $_SERVER['HTTP_REFERER'] . "\n";
        }

        $message .= "Message: " . $error->exception->getMessage() . "\n\n";
        $message .= "Trace:\n" . $error->exception->getTraceAsString() . "\n\n";
        $message .= "Request data: " . var_export($error->request->getParams(), true) . "\n\n";

        /*$it = $_SESSION;

        $message .= "Session data:\n\n";
        foreach ($it as $key => $value) {
            $message .= $key . ": " . var_export($value, true) . "\n";
        }
        $message .= "\n";*/

        $message .= "Cookie data:\n\n";
        foreach ($_COOKIE as $key => $value) {
            $message .= $key . ": " . var_export($value, true) . "\n";
        }
        $message .= "\n";

        return '<pre>' . $message . '</pre>';
    }

    public function unauthorizedAction() {
    	$this->_response->setHttpResponseCode(403);
        $this->view->setTitrePage("Accès refusé");
    }
}
