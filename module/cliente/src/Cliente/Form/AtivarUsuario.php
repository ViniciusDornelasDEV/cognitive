<?php

 namespace Cliente\Form;
 
use Application\Form\Base as BaseForm; 

 class AtivarUsuario extends BaseForm {
     
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
        $this->genericTextInput('nome', '<span class="asterisco-obrigatorio">*</span> Nome: ', true, '* Nome', 'campo-obrigatorio');

        $this->genericTextInput('sobrenome', '<span class="asterisco-obrigatorio">*</span> Sobrenome: ', true, '* Sobrenome', 'campo-obrigatorio');
        
        $estados = $this->serviceLocator->get('Estado')->getRecordsFromArray(array(), 'nome')->toArray();
        $estados = $this->prepareForDropDown($estados, array('nome', 'nome'), array('' => 'Selecionar estado'));
        $this->_addDropdown('estado', 'Estado:', false, $estados);

        $this->genericTextInput('telefone', 'Telefone: ', false, 'Telefone');

        $this->addEmailElement('login', '<span class="asterisco-obrigatorio">*</span> Email de acesso: ', false,'* Email de acesso');

        $this->_addPassword('senha', '<span class="asterisco-obrigatorio">*</span> Senha: ', 'Senha', false, true, 'campo-obrigatorio');
        
        $this->_addPassword('confirma_senha', '<span class="asterisco-obrigatorio">*</span> Confirma senha: ', 'Confirmar senha', 'senha', true, 'campo-obrigatorio');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
