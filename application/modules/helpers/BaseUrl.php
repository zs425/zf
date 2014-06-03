<?php
/**
 * Récupère l'URL root de l'application
 * 
 * @package application
 * @subpackage viewhelpers
 */
class Zend_View_Helper_BaseUrl
{
    /**
     * Récupère la baseUrl depuis le contrôleur frontal 
     * 
     * @return string
     */
    public function baseUrl()
    {
        return Zend_Controller_Front::getInstance()->getBaseUrl();
    }
}
