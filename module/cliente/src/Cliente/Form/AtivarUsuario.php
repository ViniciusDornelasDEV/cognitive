<?php

 namespace Cliente\Form;
 
use Application\Form\Base as BaseForm; 

 class AtivarUsuario extends BaseForm {
     
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

        $this->genericTextInput('sobrenome', '* Sobrenome: ', true);

        //$this->addEmailElement('login', '* Email corporativo', true);

        $this->genericTextInput('cargo', '* Cargo: ', true);

        $this->genericTextInput('pais', '* País: ', true);

        $this->genericTextInput('estado', '* Estado: ', true);

        $this->genericTextInput('telefone', '* Telefone: ', true);

        $this->_addPassword('senha', '* Senha: ', 'Senha');
        
        $this->_addPassword('confirma_senha', '* Confirma senha: ', 'Confirmar senha', 'senha');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
