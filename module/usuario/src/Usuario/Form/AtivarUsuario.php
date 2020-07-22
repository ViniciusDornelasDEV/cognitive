<?php

 namespace Usuario\Form;
 
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
        
        $this->_addPassword('senha', '* Senha: ', 'Senha');
        
        $this->_addPassword('confirma_senha', '* Confirma senha: ', 'Confirmar senha', 'senha');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
