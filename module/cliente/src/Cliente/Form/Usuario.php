<?php

 namespace Cliente\Form;
 
use Application\Form\Base as BaseForm; 

 class Usuario extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator)
    {

        parent::__construct($name); 
        $this->setServiceLocator($serviceLocator);         
        $this->genericTextInput('nome', '<span class="asterisco-obrigatorio">*</span> Nome: ', true, 'Digite seu nome', 'campo-obrigatorio');

        $this->genericTextInput('sobrenome', 'Sobrenome: ', false);

        $this->addEmailElement('login', '<span class="asterisco-obrigatorio">*</span> Email corporativo', true, 'Email de acesso', false, 'campo-obrigatorio');

        
        $estados = $this->serviceLocator->get('Estado')->getRecordsFromArray(array(), 'nome')->toArray();
        $estados = $this->prepareForDropDown($estados, array('nome', 'nome'), array('' => 'Selecionar estado'));
        $this->_addDropdown('estado', 'Estado:', false, $estados);


        $this->genericTextInput('telefone', 'Telefone: ', false);

        $this->_addDropdown('id_usuario_tipo', '<span class="asterisco-obrigatorio">*</span> Tipo de cliente:', true, array(3 => 'Cliente admin', 4 => 'Visualizar'), '', 'campo-obrigatorio');

        $this->_addDropdown('ativo', '<span class="asterisco-obrigatorio">*</span> Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'), '', 'campo-obrigatorio');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
?>



