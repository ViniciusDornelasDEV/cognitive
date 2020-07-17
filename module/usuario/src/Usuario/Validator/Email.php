<?php

namespace Usuario\Validator;
use Zend\Validator\AbstractValidator;

class Email extends AbstractValidator
{
    const FLOAT = 'float';
    private $prefixo = '';

    protected $messageTemplates = array(
        self::FLOAT => "'%value%' inválido. Tente um email com o nome de seu site. Ex: admin@meusite.com"
    );

    function __construct($prefixo){
    	$this->prefixo = $prefixo;
    	parent::__construct();
    }
    public function isValid($value)
    {
        $this->setValue($value);
        $parts = explode('@', $value);

        if(trim($parts[1]) != trim($this->prefixo)){
	    	$this->error(self::FLOAT);
	        return false;
        }
	    return true;
	    
	}
}

?>