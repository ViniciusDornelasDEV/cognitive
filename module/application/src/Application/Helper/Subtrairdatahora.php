<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Subtrairdatahora extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($dataInicio, $dataFim = false) {
    		if(!$dataFim){
    			$dataFim = date('Y-m-d H:i:s');
    		}
    		
            
            $d1 = new \DateTime($dataInicio);
            $d2 = new \DateTime($dataFim);
            
            //Calcula a diferença entre as datas
            $diff = $d1->diff($d2, true);

		    //Formata no padrão esperado e retorna
		    return $diff->format('%H:%I:%S');
        	
    }
}