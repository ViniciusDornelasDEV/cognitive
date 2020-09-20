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
        $this->genericTextInput('nome', '* Nome: ', true);

        $this->genericTextInput('sobrenome', 'Sobrenome: ', false);

        $this->addEmailElement('login', '* Email corporativo', true);

        
        $estados = $this->serviceLocator->get('Estado')->getRecordsFromArray(array(), 'nome')->toArray();
        $estados = $this->prepareForDropDown($estados, array('nome', 'nome'), array('' => 'Selecionar estado'));
        $this->_addDropdown('estado', 'Estado:', false, $estados);


        $this->genericTextInput('telefone', 'Telefone: ', false);

        $this->_addDropdown('id_usuario_tipo', '* Tipo de cliente:', true, array(3 => 'Cliente admin', 4 => 'Visualizar'));

        $this->_addDropdown('ativo', '* Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
?>



