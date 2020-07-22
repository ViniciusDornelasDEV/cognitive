<?php

 namespace Cliente\Form;
 
use Application\Form\Base as BaseForm; 

 class Usuario extends BaseForm {
     
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
        $this->genericTextInput('nome', '* Nome: ', true);

        $this->genericTextInput('sobrenome', 'Sobrenome: ', false);

        $this->addEmailElement('login', '* Email corporativo', true);

        $this->genericTextInput('cargo', 'Cargo: ', false);

        $this->genericTextInput('pais', 'País: ', false);

        $this->genericTextInput('estado', 'Estado: ', false);

        $this->genericTextInput('telefone', 'Telefone: ', false);

        $this->_addDropdown('ativo', '* Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
