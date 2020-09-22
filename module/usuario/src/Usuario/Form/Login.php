<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class Login extends BaseForm
 {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name = null)
    {
        parent::__construct($name);
        //$this->addEmailElement('login', 'Email', false, 'Email');
        $this->genericTextInput('login', 'Email', true, 'Email', 'campo-obrigatorio');
        
        $this->_addPassword('password', 'Password', 'Senha', false, true, 'campo-obrigatorio');

        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));
        $this->addSubmit('Entrar', 'btn btn-lg btn-success btn-block');
    }
 }
