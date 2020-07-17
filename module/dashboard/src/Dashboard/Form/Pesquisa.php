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
   public function __construct($name)
    {

        parent::__construct($name);          

        $this->_addDropdown('menu', 'Menu:', false, array('1' => 'Dashboard acessos', '2' => 'Dashboard financeiro'));

        $this->genericTextInput('nome', 'Nome: ', false);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
