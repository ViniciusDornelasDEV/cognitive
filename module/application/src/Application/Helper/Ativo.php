<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Ativo extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($ativo) {
      if($ativo == 'S'){
        return 'Ativo';
      }

      if($ativo == 'N'){
        return 'Inativo';
      }

      if($ativo == 'A'){
        return 'Aguardando ativação';
      }
            
    }
}