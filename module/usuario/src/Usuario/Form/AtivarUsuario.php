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
        $this->genericTextInput('login', '<span class="asterisco-obrigatorio">*</span> Email: ', false);

        $this->_addPassword('senha', '<span class="asterisco-obrigatorio">*</span> Senha: ', 'Crie uma nova senha', false, true, 'campo-obrigatorio');
        
        $this->_addPassword('confirma_senha', '<span class="asterisco-obrigatorio">*</span> Confirma senha: ', 'Confirmar nova senha', 'senha', true, 'campo-obrigatorio');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
