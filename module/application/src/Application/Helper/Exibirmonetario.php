<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Exibirmonetario extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($valor, $sifrao = true) {

        	if($sifrao){
            	return 'R$ '.number_format($valor, 2, ',', '.');
        	}else{
        		return number_format($valor, 2, ',', '.');
        	}
    }
}