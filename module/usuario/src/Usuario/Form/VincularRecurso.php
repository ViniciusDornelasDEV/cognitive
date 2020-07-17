<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 

 class VincularRecurso extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name = null, $serviceLocator = null)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      

        $this->addHiddenInput('usuario_tipo', true);  
        
        //modulo    
        $modulos = $this->serviceLocator->get('Modulo')->getRecordsFromArray(array(), 'nome_modulo');
        
        $modulos = $this->prepareForDropDown($modulos, array('id', 'nome_modulo'));
        $this->_addDropdown('modulo', '* Modulo:', false, $modulos, 'CarregaRecursos(this.value);');

        //recurso
        $serviceRecurso = $this->serviceLocator->get('Recurso');

        $recursos = $serviceRecurso->fetchAll()->toArray();
        
        $recursos = $this->prepareForDropDown($recursos, array('id', 'nome'));
        $this->_addDropdown('recurso', '* Recurso:', true, $recursos, 'BuscaDescricaoRecurso(this.value);');
        
        $this->genericTextArea('descricao_recurso', 'Descrição:', $required = false, 
                                $placeholder = false, $html = true, $min = 0, $max = 2000, 
                                $style = 'width: 100%');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
        
    }

    public function setRecursosByModulo($modulo){
        //buscar recursos
        $recursos = $this->serviceLocator->get('Recurso')->getRecords($modulo, 'modulo', array('id', 'nome'), 'nome');
        $recursos = $this->prepareForDropDown($recursos, array('id', 'nome'));

        //Setando valores
        $recursos = $this->get('recurso')->setAttribute('options', $recursos);
        return $recursos;
    }
 }
