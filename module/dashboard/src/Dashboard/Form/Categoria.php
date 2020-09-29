<?php

 namespace Dashboard\Form;
 
use Application\Form\Base as BaseForm; 

 class Categoria extends BaseForm {
     
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
        $this->genericTextInput('nome', '<span class="asterisco-obrigatorio">*</span> Nome: ', true, 'Nome da categoria', 'campo-obrigatorio');
        
        $this->addImageFileInput('icone', 'Ícone: ');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
