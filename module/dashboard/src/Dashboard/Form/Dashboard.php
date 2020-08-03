<?php

 namespace Dashboard\Form;
 
use Application\Form\Base as BaseForm; 

 class Dashboard extends BaseForm {
     
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
        
        $categorias = $this->prepareForDropDown($categorias, array('id', 'nome'));
        $this->_addDropdown('categoria', 'Categoria:', false, $categorias);

        $this->addImageFileInput('icone', 'Ícone: ', false, false, false, false, false, 'image/png, image/jpeg');
        
        $this->genericTextInput('nome', '* Nome: ', true);

        $this->genericTextInput('descricao', '* Descrição: ', true);

        $this->genericTextInput('link_power_bi', 'Link power BI: ', false);

        $this->genericTextInput('link_google', 'Link google: ', false);

        $this->_addDropdown('ativo', '* Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
