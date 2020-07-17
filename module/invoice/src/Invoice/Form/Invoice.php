<?php

 namespace Invoice\Form;
 
use Application\Form\Base as BaseForm; 

 class Invoice extends BaseForm {
     
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

        $this->genericTextInput('descricao', '* Descrição: ', true);

        $this->genericTextInput('valor', '* Valor: ', true);

        $this->genericTextInput('data_referencia', '* Data de referência: ', true);

        $this->addImageFileInput('arquivo', '* Arquivo: ');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
