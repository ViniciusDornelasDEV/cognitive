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

        $this->genericTextInput('cargo', 'Cargo: ', false, 'Cargo');

        $paises = $this->serviceLocator->get('Pais')->getRecordsFromArray(array(), 'nome')->toArray();
        $paises = $this->prepareForDropDown($paises, array('nome', 'nome'), array('' => 'Selecionar país'));
        $this->_addDropdown('pais', 'País:', false, $paises);

        
        $estados = $this->serviceLocator->get('Estado')->getRecordsFromArray(array(), 'nome')->toArray();
        $estados = $this->prepareForDropDown($estados, array('nome', 'nome'), array('' => 'Selecionar estado'));
        $this->_addDropdown('estado_br', 'Estado:', false, $estados);
        $this->genericTextInput('estado', 'Estado: ', false, 'Digite o estado');

        $this->genericTextInput('telefone', 'Telefone: ', false, 'Telefone');

        $this->addEmailElement('login', '* Email de acesso: ', true,'* Email de acesso');

        $this->_addPassword('senha', '* Senha: ', 'Senha');
        
        $this->_addPassword('confirma_senha', '* Confirma senha: ', 'Confirmar senha', 'senha');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
