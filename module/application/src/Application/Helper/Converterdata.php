<?php

/**
 * Tech Studio Limited
 * 
 * General application view isMobile helper
 * 
 * @author  Vinicius Silva <vinicius.s.dornelas@gmail.com>
 * @version 1.0
 */

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Converterdata extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($data, $apenasHoras = false) {
        if(!empty($data)){
            if(strpos($data, ' ')){
                return self::ConverteTimestamp($data, $apenasHoras);
            }else{
                return self::ConverteData($data);
            }
         }
    }
    
    private function ConverteData($data){
        @$TipoData = stristr($data, "/");
        if($TipoData != false){
            $Texto = explode("/",$data);
            return $Texto[2]."-".$Texto[1]."-".$Texto[0];
        }else{
            $Texto = explode("-",$data);
            return $Texto[2]."/".$Texto[1]."/".$Texto[0];
         }
    }
    
    private function ConverteTimestamp($data, $apenasHoras){
        $Dados = explode(" ", $data);
        $hora = explode(":", $Dados[1]);

        if($apenasHoras){
            return $hora[0].':'.$hora[1];    
        }
        return self::ConverteData($Dados[0]).' '.$hora[0].':'.$hora[1];
    }
}