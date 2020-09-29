<?php

 namespace Cliente\Form;
 
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
        $this->genericTextInput('nome', false, false, 'Nome');

        $this->_addDropdown('ativo', '<span class="asterisco-obrigatorio">*</span> Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'), '', 'form-control campo-obrigatorio');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
