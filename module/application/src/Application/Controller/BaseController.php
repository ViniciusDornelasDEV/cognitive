<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Stdlib\Parameters;

abstract class BaseController extends AbstractActionController {
    
    private $baseUrl = 'https://edelivery.local/';

    public function requestGET($url, $jwtToken, $dump = false){
        $request = new Request();
        $request->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Authorization' =>  $jwtToken
        ));

        $request->setUri($this->baseUrl.$url);
        $request->setMethod('GET');
        
        $client = new Client();
        $client->setOptions(array('sslverifypeer' => null, 'sslallowselfsigned' => null));
        $response = $client->dispatch($request);        
        
        if($dump){
            print_r($response->getBody());
            die();
        }

        return json_decode($response->getBody(), true);
    }

    public function requestPOST($url, $params, $jwtToken, $dump = false){
        $request = new Request();
        $request->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Authorization' => $jwtToken
        ));
        $request->setUri($this->baseUrl.$url);
        $request->setMethod('POST');
        $request->setPost(new Parameters($params));

        $client = new Client();
        $client->setOptions(array('sslverifypeer' => null, 'sslallowselfsigned' => null));
        $response = $client->dispatch($request);

        if($dump){
            print_r($response->getBody());
            die();
        }

        return json_decode($response->getBody(), true);
    }

    public function paramsToArray($dados){
        //params precisa ser um array
        $params = array();
        foreach ($dados as $key => $dado) {
            $params[$key] = $dado;
        }
        return $params;
    }
    
}
