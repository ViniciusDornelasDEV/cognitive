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

        $this->genericTextInput('cargo', 'Cargo: ', false);

        $paises = $this->serviceLocator->get('Pais')->getRecordsFromArray(array(), 'nome')->toArray();
        $paises = $this->prepareForDropDown($paises, array('nome', 'nome'));
        $this->_addDropdown('pais', 'País:', false, $paises);

        
        $estados = $this->serviceLocator->get('Estado')->getRecordsFromArray(array(), 'nome')->toArray();
        $estados = $this->prepareForDropDown($estados, array('nome', 'nome'));
        $this->_addDropdown('estado_br', 'Estado:', false, $estados);
        $this->genericTextInput('estado', 'Estado: ', false);


        $this->genericTextInput('telefone', 'Telefone: ', false);

        $this->_addDropdown('id_usuario_tipo', '* Tipo de cliente:', true, array(3 => 'Cliente admin', 4 => 'Visualizar'));

        $this->_addDropdown('ativo', '* Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
?>



