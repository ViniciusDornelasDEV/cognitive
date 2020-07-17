<?php

namespace Application\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Session\Container;
use Zend\Authentication\AuthenticationService;

class Formatacao extends AbstractPlugin
{
    //funções básicas para controllers
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

    public function exibirMonetario($valor, $sifrao = true) {
            if($sifrao){
                return 'R$ '.number_format($valor, 2, ',', '.');
            }else{
                return number_format($valor, 2, ',', '.');
            }
    }

    public function simNao($val){
        if($val == 'S'){
            return 'Sim';
        }else{
            return 'Não';
        }
    }
}
?>