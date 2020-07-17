<?php 
namespace Application\Service;
 
class ExceptionLog
{
 
    function logException($exception)
    {
        $mensagem = date('d/m/Y').'         |       '.$exception->getMessage()."\r\n";;

        //verifica se existe arquivo para a operacao
        $arquivo = 'public/log/exceptions/log.txt';
        $arquivo = fopen($arquivo, 'a');

        fwrite($arquivo, $mensagem);
        fclose($arquivo);
    }
}

?>