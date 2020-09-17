<?php

 namespace Invoice\Form;
 
use Application\Form\Base as BaseForm; 

 class Invoice extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name)
    {

        parent::__construct($name);          

        $this->genericTextInput('descricao', '* Descrição: ', true);

        $this->genericTextInput('valor', '* Valor: ', true);

        $this->genericTextInput('data_vencimento', '* Data de vencimento: ', true);

        $this->addImageFileInput('arquivo', 'Arquivo: ', false, false, false, false, false, 'application/pdf');
        
        $this->_addDropdown('pago', '* Status:', true, array('N' => 'Em aberto', 'S' => 'Pago'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setData($data){
      $data->data_vencimento = parent::converterData($data->data_vencimento);
      parent::setData($data);
    }

    public function getData($flag = 17){
      $data = parent::getData();
      $data['valor'] = parent::numberInsertMysql($data['valor']);

      return $data;
    }

 }
