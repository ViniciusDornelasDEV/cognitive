<?php

 namespace Cliente\Form;
 
use Application\Form\Base as BaseForm; 

 class MeusDados extends BaseForm {
     
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
        $this->genericTextInput('nome', 'Nome: ', false);

        $this->addImageFileInput('logo', 'Logo: ', false, false, false, false, false, 'image/png, image/jpeg');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
