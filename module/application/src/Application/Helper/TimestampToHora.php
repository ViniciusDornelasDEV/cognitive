<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class TimestampToHora extends AbstractHelper
{
    public function __invoke($timestamp) {
        if(empty($timestamp)){
        	return '-';
        }
        $data = explode(' ', $timestamp);
        return substr($data[1], 0, -3);
        
    }
}