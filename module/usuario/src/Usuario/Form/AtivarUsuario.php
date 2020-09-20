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
        $this->genericTextInput('login', '* Email: ', false);

        $this->_addPassword('senha', '* Senha: ', 'Crie uma nova senha');
        
        $this->_addPassword('confirma_senha', '* Confirma senha: ', 'Confirmar nova senha', 'senha');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
