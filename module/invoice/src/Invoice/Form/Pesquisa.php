<?php

 namespace Invoice\Form;
 
use Application\Form\Base as BaseForm; 

 class Pesquisa extends BaseForm {
     
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

        $this->genericTextInput('data_inicio', 'De: ', false);

        $this->genericTextInput('data_fim', 'até: ', false);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
