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
        $this->genericTextInput('nome', '<span class="asterisco-obrigatorio">*</span> Nome: ', true, 'Digite seu nome', 'campo-obrigatorio');

        $this->addImageFileInput('logo', 'Logo: "Recomendado 700x394"', false, false, false, false, false, 'image/png, image/jpeg');

        $this->genericTextInput('id_azure', 'App id na azure: ', false);

        $this->genericTextInput('usuario_azure', 'Usuário na azure: ', false);

        $this->genericTextInput('senha_azure', 'Senha na azure: ', false);
        $this->_addDropdown('ativo', '<span class="asterisco-obrigatorio">*</span> Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'), '', 'campo-obrigatorio');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
