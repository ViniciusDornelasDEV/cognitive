<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Registrese extends BaseForm
 {
     
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
        $this->genericTextInput('nome', '* Nome:', true, 'Nome');
  
        $this->genericTelInput('telefone', '* Telefone de acesso:', true, 'Telefone');
        
        $this->_addPassword('senha', '* Senha: ', 'Senha');
        
        $this->_addPassword('confirma_senha', '* Confirma senha: ', 'Confirmar senha', 'senha');

        $this->setAttributes(array(
            'role'   => 'form'
        ));

    }
 }
