<?php
/**
 * Affecte un titre à la page en cours
 * 
 * @uses helper Zend_View_Helper
 * @package application
 * @subpackage viewhelpers
 */
class Zend_View_Helper_SetTitrePage extends Zend_View_Helper_Abstract 
{
    /**
     * Variable de vue à utiliser comme titre de la page
     */
    const TITRE_PAGE_VAR = 'pageTitle';
    
    /**
     * Affecte un titre à la page en cours en affectant
     * la variable prévue à cet effet 
     * 
     * @param string $titre
     */
    public function setTitrePage($titre)
    {
        $this->view->{self::TITRE_PAGE_VAR} = $titre;
    }
}
