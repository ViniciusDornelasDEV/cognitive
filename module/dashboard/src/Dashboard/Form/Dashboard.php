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
   public function __construct($name)
    {

        parent::__construct($name);          

        $this->_addDropdown('menu', 'Menu:', false, array('' => '-- Selecione --', '1' => 'Dashboard acessos', '2' => 'Dashboard financeiro'));

        $this->addImageFileInput('icone', '* Ícone: ');

        $this->genericTextInput('nome', '* Nome: ', true);

        $this->genericTextInput('descricao', '* Descrição: ', true);

        $this->genericTextInput('link_bi', 'Link PowerBI: ', false);

        $this->genericTextInput('link_google', 'Link google: ', false);

        $this->_addDropdown('ativo', '* Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
