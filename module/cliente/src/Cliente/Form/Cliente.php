<?php

 namespace Cliente\Form;
 
use Application\Form\Base as BaseForm; 

 class Cliente extends BaseForm {
     
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

        $this->addImageFileInput('logo', 'Logo: ');

        $this->genericTextInput('id_azure', 'Cliente id na azure: ', false);

        $this->genericTextInput('usuario_azure', 'Usuário na azure: ', false);

        $this->genericTextInput('senha_azure', 'Senha na azure: ', false);

        $this->_addDropdown('ativo', '* Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
