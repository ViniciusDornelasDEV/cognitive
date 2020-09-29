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
        
        $this->_addPassword('senha_atual', '<span class="asterisco-obrigatorio">*</span> Senha atual: ', 'Senha', true, 'campo-obrigatorio');

        $this->_addPassword('senha', '<span class="asterisco-obrigatorio">*</span> Nova senha: ', 'Senha', false, true, 'campo-obrigatorio');
        
        $this->_addPassword('confirma_senha', '<span class="asterisco-obrigatorio">*</span> Confirmar senha: ', 'Confirmar senha', 'senha', true, 'campo-obrigatorio');
 
        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
