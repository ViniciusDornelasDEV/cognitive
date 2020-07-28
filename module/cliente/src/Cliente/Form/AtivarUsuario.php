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
        $this->genericTextInput('nome', '* Nome: ', true, 'Nome');

        $this->genericTextInput('sobrenome', '* Sobrenome: ', true, 'Sobrenome');

        //$this->addEmailElement('login', '* Email corporativo', true);

        $this->genericTextInput('cargo', '* Cargo: ', true, 'Cargo');

        $this->genericTextInput('pais', '* País: ', true, 'País');

        $this->genericTextInput('estado', '* Estado: ', true, 'Estado');

        $this->genericTextInput('telefone', '* Telefone: ', true, 'Telefone');

        $this->_addPassword('senha', '* Senha: ', 'Senha');
        
        $this->_addPassword('confirma_senha', '* Confirma senha: ', 'Confirmar senha', 'senha');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
