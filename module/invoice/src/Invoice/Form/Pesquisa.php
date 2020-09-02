<?php

 namespace Invoice\Form;
 
use Application\Form\Base as BaseForm; 

 class Pesquisa extends BaseForm {
     
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

        $this->genericTextInput('data_inicio', false, false, 'Data de início');

        $this->genericTextInput('data_fim', false, false, 'Data final');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function getData($flag = 17){
      $data = parent::getData();
      $data['data_inicio'] = parent::converterData($data['data_inicio']);
      $data['data_fim'] = parent::converterData($data['data_fim']);
      return $data;
    }

 }
