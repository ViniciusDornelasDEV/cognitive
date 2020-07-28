<?php

 namespace Usuario\Form;
 
use Application\Form\Base as BaseForm;
 
 class PesquisaUsuario extends BaseForm
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
        $this->genericTextInput('nome', 'Nome do usuário:', false, 'Nome do usuário');

        //TIPO DE USUÁRIO 
        $serviceTipoUsuario = $this->serviceLocator->get('UsuarioTipo');
        $tiposUsuario = $serviceTipoUsuario->fetchAll()->toArray();
        unset($tiposUsuario[2]);
        $tiposUsuario = $this->prepareForDropDown($tiposUsuario, array('id', 'perfil'));

        $this->_addDropdown('id_usuario_tipo', ' Tipo de usuário:', false, $tiposUsuario);

        
        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
