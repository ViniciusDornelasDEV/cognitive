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
        $this->genericTextInput('nome', '* Nome do usuário:', true, 'Nome do usuário', 'campo-obrigatorio');

        $this->addEmailElement('login', '* Email', true, 'Email', false, 'campo-obrigatorio');
        
        //Tipo de usuário
        $serviceTipoUsuario = $this->serviceLocator->get('UsuarioTipo');
        $tipos = $serviceTipoUsuario->fetchAll(array('id', 'perfil'), 'perfil');

        if(!$tipos){
            $tipos = array();
        }
        $tipos = $this->prepareForDropDown($tipos, array('id', 'perfil'));
        unset($tipos[3]);
        unset($tipos[4]);
        $this->_addDropdown('id_usuario_tipo', '* Tipo de usuário: ', true, $tipos, 'exibirCliente(this.value);', 'campo-obrigatorio');

        $this->_addDropdown('ativo', '* Status:', true, array('S' => 'Ativo', 'N' => 'Inativo', 'A' => 'Aguardando ativação'), '', 'campo-obrigatorio');

        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
