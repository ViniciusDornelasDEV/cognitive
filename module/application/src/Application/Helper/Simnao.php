<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Simnao extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($ativo) {
        
        if($ativo == 'S') {
            return 'Sim';
        }else{
            return 'Não';
        }
        
    }
}