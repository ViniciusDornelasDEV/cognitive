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
    
       protected function getIdentity($property = null) {
        $storage = $this->getServiceLocator()->get('session');

        if (!$storage) {
            return false;
        }

        $data = $storage->read();

        if ($property && isset($data[$property])) {
            return $data[$property];
        }

        return $data;
    }

    /**
     * Returns TRUE if session is still valid (i.e. it hasn't expired).
     * 
     * @access public
     * @return void
     */
    public function sessionIsValid() {
        return time() <= $this->getIdentity('expiry');
    }

    protected function saveToS3($bucket, $src, $filename, $type) {

        $aws = $this->getServiceLocator()->get('aws');

        $s3 = $aws->get('s3');

        $result = $s3->putObject(array(
            'Bucket' => $bucket,
            'SourceFile' => $src,
            'Key' => $filename,
            'ContentType' => $type,
        ));

        return $result;
    }

    protected function deleteFromS3($filename, $bucket) {

        $aws = $this->getServiceLocator()->get('aws');

        $s3 = $aws->get('s3');

        $result = $s3->deleteObject(array(
            'Bucket' => $bucket,
            'Key' => $filename
        ));

        return $result;
    }

    public function getExtensao($name){
        $extensao = explode('.', $name);
        return $extensao[1];
    }
}
