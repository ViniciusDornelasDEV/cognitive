<?php

 namespace Cliente\Form;
 
use Application\Form\Base as BaseForm; 

 class VincularCliente extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $idUsuario, $usuario = false)
    {

        parent::__construct($name); 
        $this->setServiceLocator($serviceLocator);         
        
        $clientes = $this->serviceLocator->get('Cliente')->getClientesNotUsuario($idUsuario)->toArray();

        if($usuario){
          //retirar os clientes que não são do cliente admin logado
          $clientesAdmin = $this->getServiceLocator()->get('UsuarioCliente')->getRecords($usuario['id'], 'usuario', array('cliente'));
          $clientesAdmin2 = array();
          foreach ($clientesAdmin as $clienteAdmin) {
            $clientesAdmin2[$clienteAdmin['cliente']] = $clienteAdmin['cliente'];
          }
          //limpar o array
          foreach ($clientes as $key => $cliente) {
            if(!in_array($cliente['id'], $clientesAdmin2)){
              unset($clientes[$key]);
            }
          }
        }

        $clientes = $this->prepareForDropDown($clientes, array('id', 'nome'));
        $this->_addDropdown('cliente', '* Clientes:', true, $clientes);
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
?>



