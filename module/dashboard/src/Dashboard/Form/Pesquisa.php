<?php

 namespace Dashboard\Form;
 
use Application\Form\Base as BaseForm; 

 class Pesquisa extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $idCliente)
    {

        parent::__construct($name);          
        $this->setServiceLocator($serviceLocator);

        $categorias = $this->serviceLocator->get('CategoriaDashboard')->getRecordsFromArray(array('cliente' => $idCliente), 'nome')->toArray();
        
        $categorias = $this->prepareForDropDown($categorias, array('id', 'nome'), array('' => 'Categoria'));
        $this->_addDropdown('categoria', '', false, $categorias, '', 'Categoria');

        $this->genericTextInput('nome', false, false, 'Nome');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
