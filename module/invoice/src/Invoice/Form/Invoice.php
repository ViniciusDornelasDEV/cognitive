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

        $this->genericTextInput('descricao', '<span class="asterisco-obrigatorio">*</span> Descrição: ', true, 'Descrição do invoice', 'campo-obrigatorio');

        $this->genericTextInput('valor', '<span class="asterisco-obrigatorio">*</span> Valor: ', true, 'Valor do invoice', 'campo-obrigatorio');

        $this->genericTextInput('data_vencimento', '<span class="asterisco-obrigatorio">*</span> Data de vencimento: ', true, 'Data de vencimento', 'campo-obrigatorio');

        $this->addImageFileInput('arquivo', 'Arquivo: ', false, false, false, false, false, 'application/pdf');
        
        $this->_addDropdown('pago', '<span class="asterisco-obrigatorio">*</span> Status:', true, array('N' => 'Em aberto', 'S' => 'Pago'), '', 'campo-obrigatorio');

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
