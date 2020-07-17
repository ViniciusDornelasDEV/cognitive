<?php

 namespace Usuario\Form;
 use Application\Form\Base as BaseForm; 

 class AlterarSenha extends BaseForm
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
        
        $this->_addPassword('senha_atual', '* Senha atual: ', 'Senha');

        $this->_addPassword('senha', '* Nova senha: ', 'Senha');
        
        $this->_addPassword('confirma_senha', '* Confirmar senha: ', 'Confirmar senha', 'senha');
 
        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
