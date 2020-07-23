<?php

 namespace Usuario\Form;
 
use Application\Form\Base as BaseForm;
 
 class RecuperarSenha extends BaseForm
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
       $this->addEmailElement('login', 'Email', false, 'Email');
        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
