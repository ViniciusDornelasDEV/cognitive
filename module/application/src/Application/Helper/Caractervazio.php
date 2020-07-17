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

class Caractervazio extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($dado) {
        if(empty($dado)){
            return '-';
         }else{
         	return $dado;
         }
    }
}