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
        $this->genericTextInput('nome', '* Nome: ', true, '* Nome');

        $this->genericTextInput('sobrenome', '* Sobrenome: ', true, '* Sobrenome');
        
        $estados = $this->serviceLocator->get('Estado')->getRecordsFromArray(array(), 'nome')->toArray();
        $estados = $this->prepareForDropDown($estados, array('nome', 'nome'), array('' => 'Selecionar estado'));
        $this->_addDropdown('estado', 'Estado:', false, $estados);

        $this->genericTextInput('telefone', 'Telefone: ', false, 'Telefone');

        $this->addEmailElement('login', '* Email de acesso: ', false,'* Email de acesso');

        $this->_addPassword('senha', '* Senha: ', 'Senha');
        
        $this->_addPassword('confirma_senha', '* Confirma senha: ', 'Confirmar senha', 'senha');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
