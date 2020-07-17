<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Exibirimagem extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($caminho) {
        if(file_exists('public/'.$caminho) && !empty($caminho)){
        	return $caminho;
        }else{
        	return '/img/semImagem.gif';
        }
        
    }
}