<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 

 class TipoUsuario extends BaseForm {
     
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

      
        $this->genericTextInput('perfil', '* Nome do perfil:', true, 'Nome do perfil');

        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));     
    }
 }
