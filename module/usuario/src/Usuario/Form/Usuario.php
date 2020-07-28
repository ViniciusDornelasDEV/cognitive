<?php

 namespace Usuario\Form;
 
use Application\Form\Base as BaseForm; 
use Usuario\Validator\Email;

 class Usuario extends BaseForm
 {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);
        $this->genericTextInput('nome', '* Nome do usuário:', true, 'Nome do usuário');

        $this->addEmailElement('login', '* Email', true, 'Email');
        
        //Tipo de usuário
        $serviceTipoUsuario = $this->serviceLocator->get('UsuarioTipo');
        $tipos = $serviceTipoUsuario->fetchAll(array('id', 'perfil'), 'perfil');

        if(!$tipos){
            $tipos = array();
        }
        $tipos = $this->prepareForDropDown($tipos, array('id', 'perfil'));
        unset($tipos[2]);
        $this->_addDropdown('id_usuario_tipo', '* Tipo de usuário: ', true, $tipos, 'exibirCliente(this.value);');

        $this->_addDropdown('ativo', '* Status:', true, array('S' => 'Ativo', 'N' => 'Inativo', 'A' => 'Aguardando ativação'));

        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
