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
        $this->addEmailElement('email', 'Email', false, 'Email');
        $this->_addPassword('password', 'Password', 'Senha');

        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));
        $this->addSubmit('Entrar', 'btn btn-lg btn-success btn-block');
    }
 }
