<?php

 namespace Usuario\Form;
 
use Application\Form\Base as BaseForm;
use Usuario\Validator\Email;

 class AlterarUsuario extends BaseForm
 {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $prefixo)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);
        $this->genericTextInput('nome', '* Nome do usuário:', true, 'Nome do usuário');

        $this->addEmailElement('email', '* Email (login): ', true, 'Email');
   
        //Tipo de usuário
        $serviceTipoUsuario = $this->serviceLocator->get('UsuarioTipo');
        $tipos = $serviceTipoUsuario->fetchAll(array('id', 'perfil'));

        if(!$tipos){
            $tipos = array();
        }
        //$tipos = $this->prepareForDropDown($tipos, array('id', 'perfil'));
        //$this->_addDropdown('id_usuario_tipo', '* Tipo de usuário: ', true, $tipos);

        $this->genericTextInput('senha', 'Alterar senha: ', false, 'Nova senha');

        
        $this->_addDropdown('autoprint', '* Impressão automática:', true, array('N' => 'Não', 'S' => 'Sim'));
        
        $this->_addDropdown('alerta', '* Alerta de novo pedido:', true, array('N' => 'Não', 'S' => 'Sim'));

        $this->_addDropdown('ativo', '* Status:', true, array('S' => 'Ativo', 'N' => 'Inativo'));

        $this->getInputFilter()->get('email')->getValidatorChain()->addValidator(new Email($prefixo));

        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
