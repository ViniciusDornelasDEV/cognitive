<?php

 namespace Application\Form;
 
use Application\Form\Base as BaseForm; 

 class Menu extends BaseForm {
     
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
        
        $this->addImageFileInput('icone', 'Ícone: ');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
