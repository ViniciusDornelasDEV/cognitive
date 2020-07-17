<?php

namespace Application\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Validator\Identical;

//use Zend\InputFilter\InputFilterProviderInterface;

/**
 * BaseForm class. This serves as the base class for all classes to extend.
 *
 * @abstract
 * @extends Form
 * @author  Vinicius Silva <vinicius.s.dornelas@gmail.com>
 * @version 1.0
 */
abstract class Base extends Form {

    protected $inputFilter;
    protected $inputFilterArray;
    
    /**
     * Service Locator object.
     * 
     * @var mixed
     * @access protected
     */
    protected $serviceLocator;

    /**
     * setupDefault function.
     * 
     * @access 	public
     * @param 	bool $fieldset (default: false)
     * @return 	void
     */
    public function setupDefault($fieldset = false) {
        return $this->addSubmit();
    }

    /**
     * Adds submit/CSRF fields to form (should exist in all).
     * 
     * @access public
     * @return BaseForm
     */
    public function addSubmit($label = 'Submit', $class = 'btn btn-success') {
        /*   $this->add(array(
          'type' => 'Zend\Form\Element\Csrf',
          'name' => 'csrf'
          )); */

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => $label,
                'class' => $class,
                'style' => 'float: right;margin-right: 10px;',
            ),
        ));

        return $this;
    }

    /**
     * Adds a generic text field to the form.
     * 
     * @access private
     * @param mixed $name
     * @param bool $label (default: false)
     * @param int $minLength (default: 1)
     * @param int $maxLength (default: 100)
     * @return void
     */
    protected function addEmailElement($name, $label = false, $required = true, $placeholder = false, $identical = false) {

        $this->add(array(
            'name' => $name,
            'attributes' => array(
                'type' => 'email',
                'required' => $required,
                'class' => 'form-control',
                'placeholder' => $placeholder,
                'id' => $name
            ),
            'options' => array(
                'label' => $label,
            ),
        ));



        $validators = array(
            array(
                'name' => 'EmailAddress',
                array('StringLength', false, array(3, 100)),
            ),
        );
        
        if ($identical) {
            $this->identicalConstraint($name, $identical, $required);
        } else {
            if($required)
                $this->setMinMaxLenght($name, $required, 3, 100);
        }

        $this->inputFilterArray[$name] = array(
            'name' => $name,
            'required' => $required,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => $validators,
        );
    }

    /**
     * Adds a generic text field to the form.
     * 
     * @access private
     * @param mixed $name
     * @param bool $label (default: false)
     * @param int $minLength (default: 1)
     * @param int $maxLength (default: 100)
     * @return void
     */
    protected function genericTextInput($name, $label = false, $required = true, $placeholder = false, $valor = '', $style = '') {

        $this->add(array(
            'name' => $name,
            'attributes' => array(
                'type' => 'text',
                'required' => $required,
                'class' => 'form-control',
                'placeholder' => $placeholder,
                'id'    => $name,
                'value' => $valor,
                'style' => $style
            ),
            'options' => array(
                'label' => $label,
            ),
        ));

        if($required) {
            $this->setMinMaxLenght($name, $required, 1, 100);
        } else {
            $this->allowEmpty($name);
        }
    }

    protected function genericTelInput($name, $label = false, $required = true, $placeholder = false) {

        $this->add(array(
            'name' => $name,
            'attributes' => array(
                'type' => 'tel',
                'required' => $required,
                'class' => 'form-control',
                'placeholder' => $placeholder,
                'id'    => $name
            ),
            'options' => array(
                'label' => $label,
            ),
        ));

        if($required) {
            $this->setMinMaxLenght($name, $required, 1, 100);
        } else {
            $this->allowEmpty($name);
        }
    }

    protected function textInputCnpj($name, $label, $required, $placeholder) {
        
        $this->add(array(
            'name' => $name,
            'attributes' => array(
                'type' => 'text',
                'required' => $required,
                'class' => 'form-control',
                'placeholder' => $placeholder,
                'id'    => $name
            ),
            'options' => array(
                'label' => $label,
            ),
        ));


        if($required) {
            $this->setMinMaxLenght($name, $required, 1, 100);
        } else {
            $this->allowEmpty($name);
        }
        
    }
    
    
        /**
     * Adds a generic text field to the form.
     * 
     * @access private
     * @param mixed $name
     * @param bool $label (default: false)
     * @param int $minLength (default: 1)
     * @param int $maxLength (default: 100)
     * @return void
     */
    protected function genericTextArea($name, $label = false, $required = false, $placeholder = false, $html = true, $min = 0, $max = 0, $style = 'width: 250px') {

        
        $this->add(array(
            'name' => $name,
            'attributes' => array(
                'type' => 'textarea',
                'required' => $required,
                'class' => 'form-control',
                'placeholder' => $placeholder,
                'rows'=> 4,
                'id' => $name,
                'style' => $style
            ),
            'options' => array(
                'label' => $label,
            ),
        ));

        $this->setMinMaxLenght($name, $required, $min, $max);
    }

    private function setMinMaxLenght($field, $required, $min, $max) {        
        
        $options =  array(
                        'encoding' => 'UTF-8',
                    );
        
        if($max != 0){
            $options['min'] = $min;
            $options['max'] = $max;    
        }
        
        $this->inputFilterArray[$field] = array(
            'name' => $field,
            'required' => $required,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => $options,
                ),
            ),
        );
    }

    /**
     * Adds a HTML5 date element
     * 
     * @access public
     * @param mixed $field
     * @return void
     */
    protected function _addGenericDateElement($name, $label = false, $required = true) {
        $this->add(array(
            'name' => $name,
            'attributes' => array(
                'type' => 'text',
                'required' => $required,
                'class' => 'form-control',
                'placeholder' => '00/00/0000',
                'id'    => $name,
                'style' => 'width: 150px;'
            ),
            'options' => array(
                'label' => $label,
            ),
        ));
        
        if($required) {
            $this->setMinMaxLenght($name, $required, 10, 10);
        } else {
            $this->allowEmpty($name);
        }
    }

    /**
     * Adds a password-type field.
     * 
     * @access protected
     * @param string $name (default: 'password')
     * @param string $label (default: 'Password')
     * @param string $identical (default: false)
     * @return void
     */
    protected function _addPassword($name = 'password', $label = false, $placeholder = false, $identical = false, $required = true) {
        $this->add(array(
            'name' => $name,
            'attributes' => array(
                'type' => 'password',
                'autocomplete' => false,
                'placeholder' => $placeholder,
                'class' => 'form-control',
                'id'    => $name
            ),
            'options' => array(
                'label' => $label,
            ),
        ));

        if ($identical) {
            $this->identicalConstraint($name, $identical, $required);
        } else {
            if($required)
                $this->setMinMaxLenght($name, $required, 1, 100);
        }
    }

    private function identicalConstraint($field, $token, $required = true) {
        $this->inputFilterArray[$field] = array(
            'name' => $field,
            'required' => $required,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 100
                    ),
                ),
                array(
                    'name' => 'Identical',
                    'options' => array(
                        'token' => $token,
                        'messages' => array(
                            Identical::NOT_SAME => ucfirst($token) .' não confere. Favor redigitar!'
                        )
                    ),
                ),
            ),
        );
    }

    /**
     * Generic radio input.
     * 
     * @access public
     * @param mixed $name
     * @param mixed $label (default: null)
     * @param bool $required (default: false)
     * @param array $valueOptions (default: array())
     * @return void
     */
    public function _addRadio($name, $label = null, $required = false, array $valueOptions = array()) {
        $label = $label ? : ucfirst($name);

        $this->add(array(
            'name' => $name,
            'type' => 'Radio',
            'attributes' => array(
                'required' => $required
            ),
            'options' => array(
                'label' => $label,
                'value_options' => $valueOptions
            )
        ));
    }

    /**
     * Generic checkbox.
     * 
     * @access public
     * @param mixed $name
     * @param mixed $label (default: null)
     * @param bool $required (default: false)
     * @return void
     */
    public function _addCheckbox($name, $label = null, $required = false, $value = 1) {     

        $this->add(array(
            'name' => $name,
            'type' => 'Checkbox',
            'attributes' => array(
                'value' => $value,
                'required' => $required,
                //'class'=> 'form-control',
                'id' => $name

            ),
            'options' => array(
                'label' => $label,
                'use_hidden_element' => true,
                'checked_value'     => "S",
                'unchecked_value'   => "N"
                
            )
        ));
    }

    /**
     * Generic <select> dropdown.
     * 
     * @access public
     * @param mixed $name
     * @param mixed $label
     * @param bool $required (default: true)
     * @param array $valueOptions (default: array())
     * @return void
     */
    public function _addDropdown($name, $label, $required = true, array $valueOptions = array(), $function = '') {
                
        $options = array(
            'label' => $label,
            'value_options' => $valueOptions,
        );


        $this->add(array(
            'name' => $name,
            'type' => 'Select',
            'attributes' => array(
                'required' => $required,
                'id' => $name,
                'class'=> 'form-control',
                'onChange' => $function,
                'style' => 'max-width: 216px;'
            ),
            'options' => $options,
        )); 
       
        /*
         * As we're using input filter, we need to make sure all fields get added to it 
         * Or else getData will not find them 
         */
        $this->inputFilterArray[$name] = array(
            'name' => $name,
            'required' => $required,
            'filters' => array(
                array('name' => 'StripTags'),
            )
        );   
    }


    public function prepareForDropDown($data, array $campos, $preparedArray = array('' => '-- selecione --')){
        if($data){
            foreach ($data as $record) {
                $preparedArray[$record[$campos[0]]] = $record[$campos[1]];
            }
        }

        return $preparedArray;
    }


    /**
     * Address fieldset.
     * 
     * @access public
     * @param mixed $name
     * @param mixed $label
     * @return void
     */
    public function _addAddressFieldset($name, $label) {
        $fieldset = new AddressFieldset($name, $this->serviceLocator);
        $this->add($fieldset);
    }

    /**
     * Generic hidden input.
     * 
     * @access public
     * @param mixed $name
     * @param bool $required (default: true)
     * @param array $attrs (default: array())
     * @return void
     */
    public function addHiddenInput($name, $required = true, array $attrs = array()) {
        $attrs['id'] = $name;
        $this->add(array(
            'name' => $name,
            'type' => 'hidden',
            'attributes' => $attrs,
            'required' => $required,
        ));
        
        
        $this->inputFilterArray[$name] = array(
            'name' => $name,
            'required' => $required,
            'filters' => array(
                array('name' => 'StripTags'),
            )
        );
        
    }

    public function genericNumberInput($name, $label, $required = false, $placeholder = '') {
        
        $this->add(array(
            'name' => $name,
            'type' => 'text',
            'attributes' => array('class' => 'form-control', 'id' => $name, 'placeholder' => $placeholder),
            'options' => array(
                'label' => $label
            )            
        ));
        
        /*
         * As we're using input filter, we need to make sure all fields get added to it 
         * Or else getData will not find them 
         */
        $this->inputFilterArray[$name] = array(
            'name' => $name,
            'required' => $required,
            'filters' => array(
                array('name' => 'StripTags'),
            ),
            'validators' => array(
                array(
                    'name' => 'float'
                )
            ),

        );
        
        
    }
    
    public function genericIntegerInput($name, $label, $required = false, $placeholder = '', $min = 1, $style = '') {
        
        $this->add(array(
            'name' => $name,
            'type' => 'number',
            'attributes' => array(
                'class' => 'form-control', 
                'id' => $name, 
                'placeholder' => $placeholder,
                'style' => $style,
                'required' => $required
            ),
            'options' => array(
                'label' => $label
            )       
        ));
        
        /*
         * As we're using input filter, we need to make sure all fields get added to it 
         * Or else getData will not find them 
         */
       $this->inputFilterArray[$name] = array(
            'name' => $name,
            'allowEmpty'=>true,
            'filters'  => array(
                array('name' => 'Int'),
            ),
            'validators' => array(
              array(
                  'name' => 'Between',
                  'options' => array(
                      'min' => $min,
                      'max' => 9000,
                  ),
              ),
            ),

        );
        
        
    }
    

    public function addImageFileInput($name, $label, $required = false, $minWidth = false, $maxWidth = false, $minHeight = false, $maxHeight = false) {
          $this->add(array(
            'required'=> $required,
            'name' => $name,
            'type' => 'File',
            'options' => array(
                'label' => $label,
            ),
            'attributes' => array(
                    'id' => $name,
                    'class' => 'filestyle'
                )
        )); 
          
        $imageSize = array();
        
        if($minWidth)
            $imageSize['minWidth'] = $minWidth;
        
        if($maxWidth)
            $imageSize['maxWidth'] = $maxWidth;
        
        if($minHeight)
            $imageSize['minHeight'] = $minHeight;
          
          
        $this->inputFilterArray[$name] = array(
            'name' => $name,
            'allow_empty'=> true,
            'validators'=>array(
                array(
                        'name'=>'filesize',
                        'options'=> array('max'=> 8388608, 'empty'=>true)
                    ),
                array(
                    'name'=> 'filemimetype',
                    'options'=> array('mimeType'=> array('image/png', 'image/jpeg'), 'magicFile'=> false) 
                ), 
                array(
                    'name'=> 'fileimagesize',
                    'options'=> $imageSize
                )
            )
        );   
                  
    }
    
    
    public function addVideoInputFile($name, $label, $required = false, $id = false, $class = 'form-control') {
        
        $attrs = array('class' => $class);
        
        if($id)
            $attrs['id'] = $id;
        
        $this->add(array(
            'required'=> $required,
            'name' => $name,
            'type' => 'File',
            'options' => array(
                'label' => $label,
            ),            
            'attributes' => $attrs,
        ));         
          

        $this->inputFilterArray[$name] = array(
            'name' => $name,
            'allow_empty'=> true,
            'validators'=>array(
                array(
                        'name'=>'filesize',
                        'options'=> array('max'=> 2147483648, 'empty'=>true)
                    ),
               array(
                    'name'=> 'filemimetype',
                    'options'=> array(
                        'mimeType'=> array('video/quicktime', 'video/x-flv', 'video/mp4', 'video/3gpp', 'video/x-ms-wmv'),
                        'magicFile'=> false
                    ) 
                ) 
            )
        ); 
    }

    
        /**
     * Make an element required.
     * 
     * @access public
     * @param mixed $field
     * @return void
     */
    public function makeRequired($field) {
        $this->inputFilterArray[$field] = array('allow_empty' => false, 'required'=>true, 'name' => $field);
    }

    /**
     * allowEmpty function.
     * 
     * @access public
     * @param mixed $field
     * @return void
     */
    public function allowEmpty($field) {
        $this->inputFilterArray[$field] = array('allow_empty' => true, 'name' => $field);
    }
    
    
    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
            if($this->inputFilterArray){
                foreach ($this->inputFilterArray as $input) {
                    $inputFilter->add($factory->createInput($input));
                }                
            }

            $this->inputFilter = $inputFilter;
        }
        
        
        return $this->inputFilter;
    }

    
    /**
     * setServiceLocator function.
     * 
     * @access public
     * @param ServiceManager $serviceLocator
     * @return void
     */
    public function setServiceLocator($serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * getServiceLocator function.
     * 
     * @access public
     * @return void
     */
    
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function converterData($Data){
        if(!empty($Data)){
            if(strpos($Data, ' ')){
                return self::ConverteTimestamp($Data);
            }else{
                return self::ConverteData($Data);
            }
         }
    }
    
    private function ConverteData($Data){
        @$TipoData = stristr($Data, "/");
        if($TipoData != false){
            $Texto = explode("/",$Data);
            return $Texto[2]."-".$Texto[1]."-".$Texto[0];
        }else{
            $Texto = explode("-",$Data);
            return $Texto[2]."/".$Texto[1]."/".$Texto[0];
         }
    }
    
    private function ConverteTimestamp($Data){
        $Dados = explode(" ", $Data);
        return self::ConverteData($Dados[0]).' '.$Dados[1];
    }

    public function numberInsertMysql($valor){
    
        if (strpos($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        return $valor;
    }

    public function exibirMonetario($valor, $cifrao = true){
        if($cifrao){
            return 'R$ '.number_format($valor, 2, ',', '.');
        }else{
            return number_format($valor, 2, ',', '.');
        }
    }

    public function desabilitarCampos($campos = false){
        if($campos){
            foreach ($campos as $campo) {
                $this->get($campo)->setAttribute('disabled', 'disabled');
            }
        }else{
            foreach ($this->getElements() as $element) {
                $element->setAttribute('disabled', 'disabled');
            }
            //$this->get('dias_meta')->setAttribute('disabled', 'disabled');*/
        }
    }

}
