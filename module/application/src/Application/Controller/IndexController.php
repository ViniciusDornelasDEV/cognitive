<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Session\Container;
use Application\Classes\JwtAdapter;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

use Application\Form\Menu as formMenu;
class IndexController extends BaseController
{


    public function homeAction(){
      
      return new ViewModel();
    }

    public function indexmenuAction(){
      $menus = array();
      for ($i=0; $i < 4; $i++) { 
        $menus[$i] = array('nome' => 'Dashboards '.$i);
      }
      $paginator = new Paginator(new ArrayAdapter($menus));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(10);
      $paginator->setPageRange(5);
      
      return new ViewModel(array(
          'menus'      => $paginator,
      ));
    }

    public function novomenuAction(){
      $formMenu = new formMenu('frmMenu');

      return new ViewModel(array(
        'formMenu'  =>  $formMenu
      ));
    }

    public function alterarmenuAction(){
      $formMenu = new formMenu('frmMenu');
      $formMenu->setData(array('nome' => 'Dashboards 1'));

      return new ViewModel(array(
        'formMenu'  =>  $formMenu
      ));
    }

}
